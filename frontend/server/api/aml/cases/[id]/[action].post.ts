import { eq } from 'drizzle-orm'
import { z } from 'zod'

// AML case workflow (§4.5 steps 6–7):
//   new → under_review (analyst) → escalated (MLRO) → sar_decided → sar_submitted (MLRO only), or dismiss → closed.
const schemas = {
  assign: z.object({ analystId: z.number().int() }),
  escalate: z.object({ note: z.string().trim().min(5).max(2000) }),
  dismiss: z.object({ outcome: z.string().trim().min(10).max(2000) }),
  'sar-decision': z.object({ grounds: z.string().trim().min(20).max(8000) }),
  'sar-submit': z.object({ ncaReference: z.string().trim().min(5).max(60) }),
}
type Action = keyof typeof schemas

export default defineEventHandler(async (event) => {
  const action = getRouterParam(event, 'action') as Action
  if (!(action in schemas)) throw createError({ statusCode: 404, statusMessage: 'Unknown action' })
  // SAR decisions and submissions are MLRO authority (§6.5).
  const user = await requirePermission(event, action === 'sar-decision' || action === 'sar-submit' ? 'sar:submit' : 'aml:write')
  const id = parseId(event)
  const input = await validateBody(event, schemas[action]) as Record<string, any>
  const db = useDb()
  const t = tables
  const c = db.select().from(t.amlCases).where(eq(t.amlCases.id, id)).get()
  if (!c) notFound('Case')
  if (c.status === 'closed' || c.status === 'sar_submitted') throw createError({ statusCode: 409, statusMessage: 'Case is closed' })

  const now = new Date()
  let patch: Partial<typeof c>
  switch (action) {
    case 'assign':
      patch = { analystId: input.analystId, status: c.status === 'new' ? 'under_review' : c.status }
      notifyUser(input.analystId, { severity: 'info', title: `AML case ${c.ref} assigned to you`, link: `/aml?case=${id}` })
      break
    case 'escalate':
      patch = { status: 'escalated' }
      notifyRole('MLRO', { severity: 'high', title: `AML case escalated: ${c.ref}`, body: input.note, link: `/aml?case=${id}` })
      break
    case 'dismiss':
      patch = { status: 'closed', outcome: input.outcome, closedAt: now }
      break
    case 'sar-decision': {
      const customer = db.select().from(t.customers).where(eq(t.customers.id, c.customerId)).get()!
      // Draft is auto-populated from case data (§4.5 step 6); the MLRO's grounds are appended.
      const draft = [
        `SUBJECT: ${customer.name} (${customer.ref}), ${customer.type}, ${customer.country}`,
        `CASE: ${c.ref}, opened ${c.openedAt.toISOString().slice(0, 10)}, risk score ${c.score}/100`,
        `RISK FACTORS: ${c.scoreFactors.map(f => f.factor).join('; ')}`,
        `GROUNDS FOR SUSPICION: ${input.grounds}`,
      ].join('\n')
      patch = { status: 'sar_decided', sarDecisionAt: now, sarDraft: draft }
      break
    }
    case 'sar-submit':
      if (c.status !== 'sar_decided') throw createError({ statusCode: 409, statusMessage: 'Record a SAR decision first' })
      patch = { status: 'sar_submitted', sarSubmittedAt: now, ncaReference: input.ncaReference, closedAt: now }
      break
  }
  const updated = db.update(t.amlCases).set(patch).where(eq(t.amlCases.id, id)).returning().get()
  // Tipping-off risk: SAR content is never written to the audit detail, only the fact of the action.
  writeAudit(actorOf(user), `aml.${action.replace('-', '_')}`, 'aml_case', id, { ref: c.ref, status: updated.status }, event)
  if (action.startsWith('sar')) runCcmCycle()
  return updated
})
