import { eq } from 'drizzle-orm'
import { z } from 'zod'

// Supplier lifecycle (§4.7): due-diligence completion, periodic review, monitoring flags, exit.
const schemas = {
  'complete-dd': z.object({ questionnaireScore: z.number().int().min(0).max(100), sanctionsStatus: z.enum(['clear', 'potential_match', 'confirmed_match']), regulatoryCritical: z.boolean() }),
  review: z.object({ note: z.string().trim().min(5).max(2000), questionnaireScore: z.number().int().min(0).max(100).optional() }),
  flag: z.object({ flag: z.string().trim().min(3).max(200) }),
  'clear-flags': z.object({ note: z.string().trim().min(5).max(1000) }),
  exit: z.object({ note: z.string().trim().min(5).max(1000) }),
}
type Action = keyof typeof schemas

const addMonths = (d: Date, m: number) => { const x = new Date(d); x.setMonth(x.getMonth() + m); return x }

export default defineEventHandler(async (event) => {
  const action = getRouterParam(event, 'action') as Action
  if (!(action in schemas)) throw createError({ statusCode: 404, statusMessage: 'Unknown action' })
  const user = await requirePermission(event, 'tprm:write')
  const id = parseId(event)
  const input = await validateBody(event, schemas[action]) as Record<string, any>
  const { suppliers } = tables
  const s = useDb().select().from(suppliers).where(eq(suppliers.id, id)).get()
  if (!s) notFound('Supplier')
  const now = new Date()

  let patch: Partial<typeof s>
  switch (action) {
    case 'complete-dd': {
      const tier = classifySupplier({ ...s, countryRisk: countryRiskOf(s.country), questionnaireScore: input.questionnaireScore, regulatoryCritical: input.regulatoryCritical })
      if (input.sanctionsStatus === 'confirmed_match') throw createError({ statusCode: 409, statusMessage: 'Confirmed sanctions match. Onboarding cannot proceed. Escalate to the MLRO.' })
      patch = { ddStatus: 'complete', questionnaireScore: input.questionnaireScore, sanctionsStatus: input.sanctionsStatus, tier, status: 'active', lastReviewAt: now, nextReviewAt: addMonths(now, SUPPLIER_REVIEW_MONTHS[tier]) }
      break
    }
    case 'review':
      patch = { lastReviewAt: now, nextReviewAt: addMonths(now, SUPPLIER_REVIEW_MONTHS[s.tier]), ...(input.questionnaireScore !== undefined ? { questionnaireScore: input.questionnaireScore } : {}) }
      break
    case 'flag':
      patch = { monitoringFlags: [...s.monitoringFlags, input.flag] }
      if (s.tier === 'critical' || s.tier === 'high') notifyRole('CCO', { severity: 'high', title: `Supplier alert: ${s.name}`, body: input.flag, link: `/tprm?id=${id}` })
      break
    case 'clear-flags':
      patch = { monitoringFlags: [] }
      break
    case 'exit':
      patch = { status: 'exited', nextReviewAt: null }
      break
  }
  const updated = useDb().update(suppliers).set(patch).where(eq(suppliers.id, id)).returning().get()
  writeAudit(actorOf(user), `supplier.${action.replace('-', '_')}`, 'supplier', id, { ref: s.ref, ...input }, event)
  return updated
})
