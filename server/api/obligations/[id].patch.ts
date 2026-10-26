import { eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'compliance:write')
  const id = parseId(event)
  const { controlIds, ...patch } = await validateBody(event, obligationBody.partial())
  const { obligations } = tables
  const before = useDb().select().from(obligations).where(eq(obligations.id, id)).get()
  if (!before) notFound('Obligation')
  const updated = useDb().update(obligations).set({ ...patch, ...(patch.status ? { lastAssessedAt: new Date() } : {}) }).where(eq(obligations.id, id)).returning().get()
  if (controlIds) setLinks('obligation', id, 'control', controlIds)
  writeAudit(actorOf(user), patch.status && patch.status !== before.status ? 'obligation.assessed' : 'obligation.updated', 'obligation', id, { ref: before.ref, from: before.status, to: updated.status }, event)
  return updated
})
