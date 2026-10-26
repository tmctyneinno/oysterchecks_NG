export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'compliance:write')
  const { controlIds, ...input } = await validateBody(event, obligationBody)
  const { obligations } = tables
  const row = useDb().insert(obligations).values({ ...input, ref: nextRef(obligations, obligations.ref, 'OB', 3), lastAssessedAt: input.status === 'not_assessed' ? null : new Date() }).returning().get()
  setLinks('obligation', row.id, 'control', controlIds ?? [])
  writeAudit(actorOf(user), 'obligation.created', 'obligation', row.id, { ref: row.ref, framework: row.framework }, event)
  return row
})
