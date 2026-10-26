export default defineEventHandler(async (event) => {
  await requirePermission(event, 'security:read')
  const rows = useDb().select().from(tables.accessAccounts).orderBy(tables.accessAccounts.system).all()
  const names = userNames(rows.map(r => r.reviewedById))
  const now = Date.now()
  return rows.map(a => {
    const age = a.lastReviewedAt ? Math.floor((now - a.lastReviewedAt.getTime()) / 86_400_000) : null
    return { ...a, reviewedByName: a.reviewedById ? names.get(a.reviewedById) ?? null : null, reviewAgeDays: age, overdue: a.privileged && (age === null || age > 90) }
  })
})
