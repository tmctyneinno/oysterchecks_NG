import { desc } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  await requirePermission(event, 'audit:read')
  const db = useDb()
  const engagements = db.select().from(tables.auditEngagements).orderBy(desc(tables.auditEngagements.plannedStart)).all()
  const findings = db.select().from(tables.auditFindings).orderBy(desc(tables.auditFindings.dueAt)).all()
  const names = userNames([...engagements.map(e => e.leadId), ...findings.map(f => f.ownerId)])
  const now = Date.now()
  return {
    engagements: engagements.map(e => ({
      ...e,
      leadName: names.get(e.leadId) ?? 'Unknown',
      findings: findings.filter(f => f.engagementId === e.id).length,
      openFindings: findings.filter(f => f.engagementId === e.id && f.status === 'open').length,
    })),
    findings: findings.map(f => ({
      ...f,
      ownerName: names.get(f.ownerId) ?? 'Unknown',
      engagementRef: engagements.find(e => e.id === f.engagementId)?.ref ?? 'Unknown',
      overdue: f.status === 'open' && f.dueAt.getTime() < now,
    })),
  }
})
