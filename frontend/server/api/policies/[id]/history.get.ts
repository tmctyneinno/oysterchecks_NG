import { desc, eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  await requirePermission(event, 'governance:read')
  const id = parseId(event)
  const rows = useDb().select().from(tables.policyVersions).where(eq(tables.policyVersions.policyId, id)).orderBy(desc(tables.policyVersions.at)).all()
  const names = userNames(rows.map(r => r.actorId))
  return rows.map(r => ({ ...r, actorName: names.get(r.actorId) ?? 'Unknown' }))
})
