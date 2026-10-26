import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  managementResponse: z.string().trim().min(5).max(4000).optional(),
  dueAt: z.coerce.date().optional(),
  close: z.object({ evidence: z.string().trim().min(10).max(4000) }).optional(),
})

/** Management response, due-date change (audit only) or closure with evidence (§4.6 step 6). */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'audit:read')
  const id = parseId(event)
  const input = await validateBody(event, body)
  const db = useDb()
  const { auditFindings: f } = tables
  const finding = db.select().from(f).where(eq(f.id, id)).get()
  if (!finding) notFound('Finding')

  const isAuditor = can(user.role, 'audit:write')
  const isOwner = finding.ownerId === user.id
  if (!isAuditor && !isOwner) throw createError({ statusCode: 403, statusMessage: 'Only the finding owner or Internal Audit can update this finding' })
  if (input.dueAt && !isAuditor) throw createError({ statusCode: 403, statusMessage: 'Only Internal Audit can change agreed due dates' })
  if (input.close && !isAuditor) throw createError({ statusCode: 403, statusMessage: 'Closure is validated by Internal Audit' })

  const updated = db.update(f).set({
    ...(input.managementResponse ? { managementResponse: input.managementResponse } : {}),
    ...(input.dueAt ? { dueAt: input.dueAt } : {}),
    ...(input.close ? { status: 'closed' as const, closedAt: new Date(), closureEvidence: input.close.evidence } : {}),
  }).where(eq(f.id, id)).returning().get()
  writeAudit(actorOf(user), input.close ? 'audit.finding_closed' : 'audit.finding_updated', 'audit_finding', id, { ref: finding.ref, dueChanged: !!input.dueAt }, event)
  runCcmCycle()
  return updated
})
