import { and, eq, ne } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ lessonsLearned: z.string().trim().min(10).max(4000) })

/** Closure & lessons learned (§4.8 step 7). Blocked while actions or a regulatory notification are outstanding. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:write')
  if (ROLES[user.role].scope === 'bu') throw createError({ statusCode: 403, statusMessage: 'Incident closure requires second-line sign-off' })
  const id = parseId(event)
  const { lessonsLearned } = await validateBody(event, body)
  const db = useDb()
  const { incidents, actions } = tables
  const i = db.select().from(incidents).where(eq(incidents.id, id)).get()
  if (!i) notFound('Incident')
  if (!i.rootCause) throw createError({ statusCode: 409, statusMessage: 'Record the root cause before closing' })
  if (i.notifiable && !i.notifiedAt) throw createError({ statusCode: 409, statusMessage: `Regulatory notification to ${i.regulator} is outstanding` })
  const open = db.select({ id: actions.id }).from(actions).where(and(eq(actions.sourceType, 'incident'), eq(actions.sourceId, id), ne(actions.status, 'done'))).all()
  if (open.length) throw createError({ statusCode: 409, statusMessage: `${open.length} corrective action(s) still open` })

  const updated = db.update(incidents).set({ status: 'closed', lessonsLearned, closedAt: new Date(), closedById: user.id }).where(eq(incidents.id, id)).returning().get()
  writeAudit(actorOf(user), 'incident.closed', 'incident', id, { ref: i.ref }, event)
  return updated
})
