import { desc } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'hr:read')
  const rows = useDb().select().from(tables.employees).orderBy(desc(tables.employees.startDate)).all()
  const now = Date.now()
  return maskPii(rows, user.role).map(e => ({
    ...e,
    daysToStart: Math.ceil((e.startDate.getTime() - now) / 86_400_000),
    atRisk: e.vettingStatus !== 'cleared' && !e.terminatedAt && e.startDate.getTime() - now < 3 * 86_400_000,
  }))
})
