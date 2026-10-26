export default defineEventHandler(async (event) => {
  await requirePermission(event, 'integrations:read')
  const { demoSimulator } = useRuntimeConfig()
  const rows = useDb().select().from(tables.integrations).all()
  const now = Date.now()
  return {
    demoSimulator,
    integrations: rows.map(i => ({ ...i, minutesSinceSync: i.lastSyncAt ? Math.round((now - i.lastSyncAt.getTime()) / 60_000) : null })),
  }
})
