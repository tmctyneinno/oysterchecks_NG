export default defineEventHandler(async (event) => {
  await requirePermission(event, 'compliance:read')
  const rows = useDb().select().from(tables.obligations).orderBy(tables.obligations.ref).all()
  const names = userNames(rows.map(r => r.ownerId))
  return rows.map(o => ({
    ...o,
    ownerName: names.get(o.ownerId) ?? 'Unknown',
    controlIds: linkedIds('obligation', o.id, 'control'),
    policyIds: linkedIds('obligation', o.id, 'policy'),
    evidenceCount: listEvidence('obligation', o.id).length,
  }))
})
