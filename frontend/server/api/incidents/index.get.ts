import { desc, eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:read')
  const { incidents, actions } = tables
  const db = useDb()
  const rows = db.select().from(incidents).where(buScope(user, incidents.businessUnit)).orderBy(desc(incidents.reportedAt)).all()
  const acts = db.select().from(actions).where(eq(actions.sourceType, 'incident')).all()
  const names = userNames(rows.flatMap(r => [r.ownerId, r.reportedById]))
  const now = Date.now()
  return rows.map(i => {
    const own = acts.filter(a => a.sourceId === i.id)
    return {
      ...i,
      ownerName: i.ownerId ? names.get(i.ownerId) ?? null : null,
      reportedByName: names.get(i.reportedById) ?? 'Unknown',
      actionsOpen: own.filter(a => a.status !== 'done').length,
      actionsTotal: own.length,
      notificationOverdue: i.notifiable && !i.notifiedAt && !!i.notificationDueAt && i.notificationDueAt.getTime() < now,
    }
  })
})
