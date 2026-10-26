import { desc } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  await requirePermission(event, 'compliance:read')
  const rows = useDb().select().from(tables.regulatoryReports).orderBy(desc(tables.regulatoryReports.dueAt)).all()
  const names = userNames(rows.flatMap(r => [r.ownerId, r.submittedById]))
  const now = Date.now()
  return rows.map(r => ({
    ...r,
    ownerName: names.get(r.ownerId) ?? 'Unknown',
    submittedByName: r.submittedById ? names.get(r.submittedById) ?? null : null,
    state: (r.submittedAt
      ? (r.submittedAt <= r.dueAt ? 'submitted' : 'submitted_late')
      : r.dueAt.getTime() < now ? 'overdue' : r.dueAt.getTime() - now < 3 * 86_400_000 ? 'due_soon' : 'upcoming') as 'submitted' | 'submitted_late' | 'overdue' | 'due_soon' | 'upcoming',
  }))
})
