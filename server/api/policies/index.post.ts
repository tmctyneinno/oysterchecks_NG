export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'governance:write')
  const { riskIds, controlIds, obligationIds, ...input } = await validateBody(event, policyBody)
  const { policies, policyVersions } = tables
  const db = useDb()
  const row = db.insert(policies).values({ ...input, ref: nextRef(policies, policies.ref, 'POL', 3), status: 'draft', version: 1 }).returning().get()
  db.insert(policyVersions).values({ policyId: row.id, version: 1, status: 'draft', note: 'Created', actorId: user.id, at: new Date() }).run()
  setLinks('policy', row.id, 'risk', riskIds ?? [])
  setLinks('policy', row.id, 'control', controlIds ?? [])
  setLinks('policy', row.id, 'obligation', obligationIds ?? [])
  writeAudit(actorOf(user), 'policy.created', 'policy', row.id, { ref: row.ref }, event)
  return row
})
