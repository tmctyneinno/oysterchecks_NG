import { eq } from 'drizzle-orm'

/** Manual re-sync. With real connectors this enqueues a job; in demo mode it records the attempt. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'integrations:manage')
  const id = getRouterParam(event, 'id')!
  const { integrations } = tables
  const row = useDb().select().from(integrations).where(eq(integrations.id, id)).get()
  if (!row) notFound('Integration')
  if (row.status === 'disconnected') throw createError({ statusCode: 409, statusMessage: `${row.name} is disconnected. Check credentials in the connector configuration` })
  const updated = useDb().update(integrations).set({ lastSyncAt: new Date() }).where(eq(integrations.id, id)).returning().get()
  writeAudit(actorOf(user), 'integration.sync_requested', 'integration', id, { name: row.name }, event)
  return updated
})
