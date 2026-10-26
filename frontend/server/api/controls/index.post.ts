export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'controls:write')
  const { riskIds, ...input } = await validateBody(event, controlBody)
  const { controls } = tables
  const row = useDb().insert(controls).values({ ...input, ref: nextRef(controls, controls.ref, 'C', 3) }).returning().get()
  for (const riskId of riskIds ?? []) setLinks('risk', riskId, 'control', [...linkedIds('risk', riskId, 'control'), row.id])
  writeAudit(actorOf(user), 'control.created', 'control', row.id, { ref: row.ref }, event)
  return row
})
