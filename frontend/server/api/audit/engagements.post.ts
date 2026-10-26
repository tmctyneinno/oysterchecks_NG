import { z } from 'zod'

const body = z.object({
  title: z.string().trim().min(3).max(200),
  scope: z.string().trim().max(2000).optional(),
  businessUnit: z.string().min(2),
  leadId: z.number().int(),
  plannedStart: z.coerce.date(),
  plannedEnd: z.coerce.date(),
}).refine(b => b.plannedEnd >= b.plannedStart, 'End must be after start')

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'audit:write')
  const input = await validateBody(event, body)
  const { auditEngagements: e } = tables
  const year = input.plannedStart.getFullYear()
  const row = useDb().insert(e).values({ ...input, ref: nextRef(e, e.ref, `IA-${year}`, 2), status: 'planned' }).returning().get()
  writeAudit(actorOf(user), 'audit.engagement_planned', 'audit_engagement', row.id, { ref: row.ref }, event)
  return row
})
