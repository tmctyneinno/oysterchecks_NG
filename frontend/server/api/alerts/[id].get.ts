import { asc, eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'alerts:read')
  const id = parseId(event)
  const db = useDb()
  const { alerts, alertEvents, ccmRules } = tables
  const alert = db.select().from(alerts).where(whereAll(eq(alerts.id, id), buScope(user, alerts.businessUnit))).get()
  if (!alert) notFound('Alert')

  const events = db.select().from(alertEvents).where(eq(alertEvents.alertId, id)).orderBy(asc(alertEvents.at)).all()
  const rule = alert.ruleId ? db.select().from(ccmRules).where(eq(ccmRules.id, alert.ruleId)).get() : null
  const names = userNames([alert.assigneeId, alert.closedById, alert.ownerUserId])
  const lastClosureRequest = [...events].reverse().find(e => e.type === 'closure_requested')

  return {
    ...alert,
    assigneeName: alert.assigneeId ? names.get(alert.assigneeId) ?? null : null,
    closedByName: alert.closedById ? names.get(alert.closedById) ?? null : null,
    escalationSteps: alert.escalationPath.map((t, i) => ({ label: ESCALATION_TARGETS[t].label, reached: i <= alert.escalationLevel, current: i === alert.escalationLevel })),
    slaLabel: slaLabel(alert.sla),
    rule: rule ? { id: rule.id, name: rule.name, description: rule.description, dataSource: rule.dataSource } : null,
    events,
    evidence: listEvidence('alert', id),
    // Four-eyes: whoever requested closure cannot sign it off (§4.4 step 7).
    canSignOff: can(user.role, 'alerts:close') && alert.status === 'pending_closure' && lastClosureRequest?.actorId !== user.id,
  }
})
