import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  status: z.enum(['planned', 'in_progress', 'complete']).optional(),
  rating: z.enum(['satisfactory', 'needs_improvement', 'unsatisfactory']).nullable().optional(),
  controlIds: z.array(z.number().int()).max(50).optional(),
}).refine(b => b.status !== 'complete' || b.rating, 'A completed activity needs a rating')

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'assurance:write')
  const id = parseId(event)
  const { controlIds, ...patch } = await validateBody(event, body)
  const updated = useDb().update(tables.assuranceActivities).set(patch).where(eq(tables.assuranceActivities.id, id)).returning().get()
  if (!updated) notFound('Activity')
  if (controlIds) setLinks('assurance', id, 'control', controlIds)
  writeAudit(actorOf(user), 'assurance.updated', 'assurance_activity', id, { ...patch }, event)
  return updated
})
