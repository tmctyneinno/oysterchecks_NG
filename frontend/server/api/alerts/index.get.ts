import { desc, eq, gte, inArray, ne, or } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'alerts:read')
  const q = getQuery(event)
  const { alerts } = tables
  const view = String(q.view ?? 'open')

  const statusFilter = view === 'closed' ? eq(alerts.status, 'closed')
    : view === 'all' ? or(ne(alerts.status, 'closed'), gte(alerts.closedAt, new Date(Date.now() - 30 * 86_400_000)))
      : inArray(alerts.status, [...OPEN_STATUSES])

  const rows = useDb().select().from(alerts)
    .where(whereAll(statusFilter, buScope(user, alerts.businessUnit), q.severity ? eq(alerts.severity, String(q.severity) as Severity) : undefined))
    .orderBy(desc(alerts.detectedAt)).limit(500).all()

  const names = userNames(rows.map(r => r.assigneeId))
  return rows.map(r => ({
    ...r,
    assigneeName: r.assigneeId ? names.get(r.assigneeId) ?? null : null,
    escalationLabel: escalationPathLabel(r.escalationPath),
    currentLevelLabel: ESCALATION_TARGETS[r.escalationPath[r.escalationLevel]!]?.label ?? null,
    slaLabel: slaLabel(r.sla),
  }))
})
