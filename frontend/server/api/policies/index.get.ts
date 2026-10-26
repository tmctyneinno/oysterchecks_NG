import { sql } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'governance:read')
  const db = useDb()
  const { policies, policyAcknowledgements: acks, users } = tables
  const rows = db.select().from(policies).orderBy(policies.ref).all()
  const activeUsers = db.select({ n: sql<number>`count(*)` }).from(users).where(sql`${users.active} = 1`).get()?.n ?? 0
  const allAcks = db.select().from(acks).all()
  const names = userNames(rows.flatMap(p => [p.ownerId, p.approvedById]))
  const now = Date.now()
  return rows.map((p) => {
    const current = allAcks.filter(a => a.policyId === p.id && a.version === p.version)
    return {
      ...p,
      ownerName: names.get(p.ownerId) ?? 'Unknown',
      approvedByName: p.approvedById ? names.get(p.approvedById) ?? null : null,
      ackCount: current.length,
      ackTarget: activeUsers,
      acknowledgedByMe: current.some(a => a.userId === user.id),
      reviewOverdue: p.status !== 'retired' && p.reviewDate.getTime() < now,
      riskIds: linkedIds('policy', p.id, 'risk'),
      controlIds: linkedIds('policy', p.id, 'control'),
      obligationIds: linkedIds('policy', p.id, 'obligation'),
    }
  })
})
