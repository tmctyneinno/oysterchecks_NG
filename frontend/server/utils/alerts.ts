import { and, desc, eq, gte, notInArray } from 'drizzle-orm'
import type { Breach } from '../ccm/evaluators'

type AlertRow = typeof tables.alerts.$inferSelect

export function addAlertEvent(alertId: number, actor: Actor, type: string, note?: string) {
  useDb().insert(tables.alertEvents).values({ alertId, at: new Date(), actorId: actor.id, actorName: actor.name, type, note }).run()
}

/** Resolves who is accountable at an escalation level (§4.4 step 5). */
export function resolveTarget(target: EscalationTarget, alert: Pick<AlertRow, 'ownerUserId' | 'businessUnit'>) {
  const def = ESCALATION_TARGETS[target]
  if (def.dynamic === 'owner' && alert.ownerUserId) return { label: def.label, userIds: [alert.ownerUserId] }
  if (def.dynamic === 'bu_manager') {
    const managers = usersWithRole('BU_MANAGER', alert.businessUnit)
    if (managers.length) return { label: def.label, userIds: managers.map(m => m.id) }
  }
  if (def.role) return { label: def.label, userIds: usersWithRole(def.role).map(u => u.id) }
  return { label: def.label, userIds: [] as number[] }
}

const link = (id: number) => `/alerts?id=${id}`

function notifyTarget(alert: AlertRow, target: EscalationTarget, title: string) {
  const { label, userIds } = resolveTarget(target, alert)
  for (const id of userIds) notifyUser(id, { severity: alert.severity, title, body: alert.title, link: link(alert.id) })
  return { label, userIds }
}

export interface NewAlert {
  source: AlertRow['source']
  ruleId?: string | null
  dedupeKey?: string | null
  entityType?: string | null
  entityId?: number | null
  title: string
  detail?: string | null
  module: string
  businessUnit?: string | null
  ownerUserId?: number | null
  severity: Severity
  sla: SlaSpec
  escalationPath: EscalationTarget[]
  entityDueAt?: Date | null
}

export function createAlert(input: NewAlert, actor: Actor = SYSTEM_ACTOR) {
  const db = useDb()
  const now = new Date()
  const firstTarget = input.escalationPath[0]
  const assignee = firstTarget ? resolveTarget(firstTarget, { ownerUserId: input.ownerUserId ?? null, businessUnit: input.businessUnit ?? null }).userIds[0] : undefined
  const ref = nextRef(tables.alerts, tables.alerts.ref, 'ALT', 6)
  const row = db.insert(tables.alerts).values({
    ref,
    source: input.source,
    ruleId: input.ruleId ?? null,
    dedupeKey: input.dedupeKey ?? null,
    entityType: input.entityType ?? null,
    entityId: input.entityId ?? null,
    title: input.title,
    detail: input.detail ?? null,
    module: input.module,
    businessUnit: input.businessUnit ?? null,
    ownerUserId: input.ownerUserId ?? null,
    severity: input.severity,
    status: 'open',
    assigneeId: assignee ?? null,
    escalationPath: input.escalationPath,
    escalationLevel: 0,
    sla: input.sla,
    detectedAt: now,
    ackDueAt: new Date(now.getTime() + ACK_MINUTES[input.severity] * 60_000),
    dueAt: computeDueAt(input.sla, now, input.entityDueAt),
  }).returning().get()

  addAlertEvent(row.id, actor, 'detected', input.source === 'manual' ? 'Raised manually' : `Raised by ${input.ruleId ?? (input.source === 'kri' ? 'KRI monitor' : 'action monitor')}`)
  if (firstTarget) {
    const { label } = notifyTarget(row, firstTarget, `${row.severity.toUpperCase()} alert ${row.ref}`)
    addAlertEvent(row.id, SYSTEM_ACTOR, 'routed', `Routed to ${label}`)
  }
  writeAudit(actor, 'alert.created', 'alert', row.id, { ref: row.ref, ruleId: row.ruleId, severity: row.severity })
  publish({ type: 'alert', payload: { id: row.id, ref: row.ref, severity: row.severity, status: row.status, title: row.title, businessUnit: row.businessUnit, change: 'created' } })
  return row
}

const REOPEN_COOLDOWN_MS = 24 * 3_600_000

/** Raises an alert for a breach unless one is already open (or was closed within the cooldown). */
export function raiseFromBreach(base: Omit<NewAlert, 'title' | 'detail' | 'entityType' | 'entityId' | 'dedupeKey' | 'businessUnit' | 'ownerUserId' | 'entityDueAt'>, prefix: string, breach: Breach) {
  const db = useDb()
  const dedupeKey = `${prefix}:${breach.key}`
  const existing = db.select({ id: tables.alerts.id, status: tables.alerts.status, closedAt: tables.alerts.closedAt })
    .from(tables.alerts).where(eq(tables.alerts.dedupeKey, dedupeKey)).orderBy(desc(tables.alerts.id)).limit(1).get()
  if (existing && (existing.status !== 'closed' || (existing.closedAt && Date.now() - existing.closedAt.getTime() < REOPEN_COOLDOWN_MS))) return null
  return createAlert({ ...base, dedupeKey, ...breach })
}

export function emitAlertUpdate(row: AlertRow) {
  publish({ type: 'alert', payload: { id: row.id, ref: row.ref, severity: row.severity, status: row.status, title: row.title, businessUnit: row.businessUnit, change: 'updated' } })
}

/**
 * Escalation pass (§4.4 step 5): unacknowledged past the ack SLA → level 1; past the resolution SLA →
 * one further level per escalation step elapsed. Critical overdue items also go to the Board Audit Committee.
 */
export function runEscalation(now = new Date()) {
  const db = useDb()
  const { alerts, alertEvents } = tables
  const open = db.select().from(alerts).where(notInArray(alerts.status, ['closed', 'pending_closure'])).all()
  let escalated = 0
  for (const a of open) {
    const ackOverdue = !a.acknowledgedAt && now > a.ackDueAt
    const overdue = now > a.dueAt
    let target = 0
    if (ackOverdue) target = 1
    if (overdue) target = Math.max(1, 1 + Math.floor((now.getTime() - a.dueAt.getTime()) / escalationStepMs(a.sla)))
    target = Math.min(target, a.escalationPath.length - 1)

    const patch: Partial<AlertRow> = {}
    if (overdue !== a.overdue) patch.overdue = overdue

    if (target > a.escalationLevel) {
      const step = a.escalationPath[target]!
      const { label, userIds } = notifyTarget(a, step, `Escalated: ${a.ref} (${a.severity.toUpperCase()})`)
      patch.escalationLevel = target
      patch.status = 'escalated'
      if (userIds[0]) patch.assigneeId = userIds[0]
      const reason = overdue ? 'resolution SLA breached' : 'not acknowledged within SLA'
      addAlertEvent(a.id, SYSTEM_ACTOR, 'escalated', `Escalated to ${label}: ${reason}${userIds.length ? '' : ' (notified by email)'}`)
      writeAudit(SYSTEM_ACTOR, 'alert.escalated', 'alert', a.id, { ref: a.ref, level: target, to: label, reason })
      escalated++
    }

    if (a.severity === 'critical' && overdue) {
      const already = db.select({ id: alertEvents.id }).from(alertEvents)
        .where(and(eq(alertEvents.alertId, a.id), eq(alertEvents.type, 'board_notified'))).get()
      if (!already) {
        notifyRole('BOARD', { severity: 'critical', title: `Critical overdue: ${a.ref}`, body: a.title, link: link(a.id) })
        addAlertEvent(a.id, SYSTEM_ACTOR, 'board_notified', 'Board Audit Committee notified: critical item overdue')
      }
    }

    if (Object.keys(patch).length) {
      const updated = db.update(alerts).set(patch).where(eq(alerts.id, a.id)).returning().get()
      emitAlertUpdate(updated)
    }
  }
  return { escalated }
}

/** 14-day alert volume by severity for the executive trend chart. */
export function alertVolumeSeries(days = 14, scopeBu?: string | null) {
  const since = new Date(Date.now() - days * 86_400_000)
  since.setHours(0, 0, 0, 0)
  const rows = useDb().select({ at: tables.alerts.detectedAt, severity: tables.alerts.severity, bu: tables.alerts.businessUnit })
    .from(tables.alerts).where(gte(tables.alerts.detectedAt, since)).all()
    .filter(r => !scopeBu || r.bu === scopeBu)
  const buckets = Array.from({ length: days }, (_, i) => {
    const d = new Date(since.getTime() + (i + 1) * 86_400_000)
    return { date: d.toLocaleDateString('en-CA'), critical: 0, high: 0, medium: 0, low: 0 }
  })
  for (const r of rows) {
    const b = buckets.find(x => x.date === r.at.toLocaleDateString('en-CA'))
    if (b) b[r.severity]++
  }
  return buckets
}

export const OPEN_STATUSES = ['open', 'acknowledged', 'investigating', 'escalated', 'pending_closure'] as const
export const isOpenStatus = (s: string) => (OPEN_STATUSES as readonly string[]).includes(s)
