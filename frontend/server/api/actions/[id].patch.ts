import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  status: z.enum(['open', 'in_progress', 'done']),
  evidenceNote: z.string().trim().max(2000).optional(),
}).refine(b => b.status !== 'done' || (b.evidenceNote && b.evidenceNote.length >= 5), { message: 'Closure evidence is required', path: ['evidenceNote'] })

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:read')
  const id = parseId(event)
  const input = await validateBody(event, body)
  const { actions } = tables
  const a = useDb().select().from(actions).where(eq(actions.id, id)).get()
  if (!a) notFound('Action')
  if (a.ownerId !== user.id && !can(user.role, 'incidents:write')) throw createError({ statusCode: 403, statusMessage: 'Only the action owner can update it' })
  const updated = useDb().update(actions).set({ ...input, completedAt: input.status === 'done' ? new Date() : null }).where(eq(actions.id, id)).returning().get()
  writeAudit(actorOf(user), 'action.updated', 'action', id, { ref: a.ref, status: input.status }, event)
  runCcmCycle()
  return updated
})
