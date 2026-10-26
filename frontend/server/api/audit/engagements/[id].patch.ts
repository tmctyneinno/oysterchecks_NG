import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  status: z.enum(['planned', 'fieldwork', 'reporting', 'closed']).optional(),
  rating: z.enum(['satisfactory', 'needs_improvement', 'unsatisfactory']).nullable().optional(),
  scope: z.string().trim().max(2000).optional(),
}).refine(b => b.status !== 'closed' || b.rating, 'Closing an engagement requires an overall rating')

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'audit:write')
  const id = parseId(event)
  const patch = await validateBody(event, body)
  const updated = useDb().update(tables.auditEngagements).set(patch).where(eq(tables.auditEngagements.id, id)).returning().get()
  if (!updated) notFound('Engagement')
  writeAudit(actorOf(user), 'audit.engagement_updated', 'audit_engagement', id, { ref: updated.ref, ...patch }, event)
  return updated
})
