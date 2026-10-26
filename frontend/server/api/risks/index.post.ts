export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'risk:write')
  const { controlIds, ...input } = await validateBody(event, riskBody)
  const { risks } = tables
  const row = useDb().insert(risks).values({ ...input, ref: nextRef(risks, risks.ref, 'R', 3), source: 'manual', lastReviewedAt: new Date() }).returning().get()
  setLinks('risk', row.id, 'control', controlIds ?? [])
  writeAudit(actorOf(user), 'risk.created', 'risk', row.id, { ref: row.ref, inherent: row.impact * row.likelihood }, event)
  return row
})
