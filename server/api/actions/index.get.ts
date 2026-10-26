import { asc } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:read')
  const rows = useDb().select().from(tables.actions).orderBy(asc(tables.actions.dueAt)).all()
  const names = userNames(rows.map(r => r.ownerId))
  const now = Date.now()
  const visible = ROLES[user.role].scope === 'bu' ? rows.filter(a => a.ownerId === user.id) : rows
  return visible.map(a => ({ ...a, ownerName: names.get(a.ownerId) ?? 'Unknown', overdue: a.status !== 'done' && a.dueAt.getTime() < now }))
})
