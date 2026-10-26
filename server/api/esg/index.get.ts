export default defineEventHandler(async (event) => {
  await requirePermission(event, 'esg:read')
  const rows = useDb().select().from(tables.esgMetrics).orderBy(tables.esgMetrics.pillar, tables.esgMetrics.framework).all()
  const names = userNames(rows.map(r => r.ownerId))
  return rows.map(m => ({ ...m, ownerName: names.get(m.ownerId) ?? 'Unknown', evidenceCount: listEvidence('esg_metric', m.id).length }))
})
