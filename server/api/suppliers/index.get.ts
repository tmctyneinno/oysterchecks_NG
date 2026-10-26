export default defineEventHandler(async (event) => {
  await requirePermission(event, 'tprm:read')
  const rows = useDb().select().from(tables.suppliers).orderBy(tables.suppliers.ref).all()
  const names = userNames(rows.map(r => r.ownerId))
  const now = Date.now()
  return rows.map(s => ({
    ...s,
    ownerName: names.get(s.ownerId) ?? 'Unknown',
    reviewOverdue: s.status === 'active' && !!s.nextReviewAt && s.nextReviewAt.getTime() < now,
    renewalSoon: !!s.contractRenewalAt && s.contractRenewalAt.getTime() - now < 90 * 86_400_000 && s.contractRenewalAt.getTime() > now,
  }))
})
