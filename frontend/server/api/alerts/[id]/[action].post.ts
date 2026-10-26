import { desc, eq } from 'drizzle-orm'
import { z } from 'zod'

// Alert workflow actions (§4.4 steps 4–7):
//   acknowledge → assign → remediation plan → request closure (with evidence) → sign-off by a
//   different authorised user (approve closes, reject reopens). Every step is timelined and audited.

const schemas = {
  acknowledge: z.object({}),
  assign: z.object({ assigneeId: z.number().int() }),
  note: z.object({ note: z.string().trim().min(1).max(4000) }),
  remediation: z.object({ plan: z.string().trim().min(5).max(4000) }),
  escalate: z.object({ reason: z.string().trim().min(3).max(500) }),
  'request-closure': z.object({ evidence: z.string().trim().min(5).max(4000), falsePositive: z.boolean().default(false) }),
  'sign-off': z.object({ decision: z.enum(['approve', 'reject']), note: z.string().trim().max(2000).optional() }),
}
type Action = keyof typeof schemas

export default defineEventHandler(async (event) => {
  const action = getRouterParam(event, 'action') as Action
  if (!(action in schemas)) throw createError({ statusCode: 404, statusMessage: 'Unknown action' })
  const user = await requirePermission(event, action === 'sign-off' ? 'alerts:close' : 'alerts:act')
  const id = parseId(event)
  const input = await validateBody(event, schemas[action]) as Record<string, any>

  const db = useDb()
  const { alerts, alertEvents } = tables
  const alert = db.select().from(alerts).where(whereAll(eq(alerts.id, id), buScope(user, alerts.businessUnit))).get()
  if (!alert) notFound('Alert')
  if (alert.status === 'closed') throw createError({ statusCode: 409, statusMessage: 'Alert is already closed' })

  const actor = actorOf(user)
  const now = new Date()
  let patch: Partial<typeof alerts.$inferInsert> = {}
  let note: string | undefined

  switch (action) {
    case 'acknowledge':
      if (alert.acknowledgedAt) throw createError({ statusCode: 409, statusMessage: 'Already acknowledged' })
      patch = { acknowledgedAt: now, status: alert.status === 'open' ? 'acknowledged' : alert.status, assigneeId: alert.assigneeId ?? user.id }
      break
    case 'assign': {
      const names = userNames([input.assigneeId])
      if (!names.size) throw createError({ statusCode: 400, statusMessage: 'Unknown user' })
      patch = { assigneeId: input.assigneeId }
      note = `Assigned to ${names.get(input.assigneeId)}`
      notifyUser(input.assigneeId, { severity: alert.severity, title: `Alert ${alert.ref} assigned to you`, body: alert.title, link: `/alerts?id=${alert.id}` })
      break
    }
    case 'note':
      note = input.note
      break
    case 'remediation':
      patch = { remediationPlan: input.plan, status: 'investigating', acknowledgedAt: alert.acknowledgedAt ?? now }
      note = input.plan
      break
    case 'escalate': {
      const next = Math.min(alert.escalationLevel + 1, alert.escalationPath.length - 1)
      if (next === alert.escalationLevel) throw createError({ statusCode: 409, statusMessage: 'Already at the top of the escalation path' })
      const target = resolveTarget(alert.escalationPath[next]!, alert)
      for (const uid of target.userIds) notifyUser(uid, { severity: alert.severity, title: `Escalated: ${alert.ref}`, body: alert.title, link: `/alerts?id=${alert.id}` })
      patch = { escalationLevel: next, status: 'escalated', assigneeId: target.userIds[0] ?? alert.assigneeId }
      note = `Escalated to ${target.label}: ${input.reason}`
      break
    }
    case 'request-closure':
      patch = { status: 'pending_closure', closureEvidence: input.evidence, falsePositive: input.falsePositive, closureRequestedAt: now, acknowledgedAt: alert.acknowledgedAt ?? now }
      note = `${input.falsePositive ? 'False positive. ' : ''}${input.evidence}`
      notifyRole('CCO', { severity: 'info', title: `Closure sign-off requested: ${alert.ref}`, body: alert.title, link: `/alerts?id=${alert.id}` })
      break
    case 'sign-off': {
      if (alert.status !== 'pending_closure') throw createError({ statusCode: 409, statusMessage: 'Closure has not been requested' })
      const request = db.select().from(alertEvents).where(eq(alertEvents.alertId, id)).orderBy(desc(alertEvents.id)).all().find(e => e.type === 'closure_requested')
      if (request?.actorId === user.id) throw createError({ statusCode: 403, statusMessage: 'Four-eyes rule: you requested this closure, so another authorised user must sign it off' })
      patch = input.decision === 'approve'
        ? { status: 'closed', closedAt: now, closedById: user.id, overdue: false }
        : { status: 'investigating', closureRequestedAt: null }
      note = input.note
      break
    }
  }

  const eventType = { acknowledge: 'acknowledged', assign: 'assigned', note: 'note', remediation: 'remediation_plan', escalate: 'escalated', 'request-closure': 'closure_requested', 'sign-off': input.decision === 'approve' ? 'closed' : 'closure_rejected' }[action]
  const updated = Object.keys(patch).length ? db.update(alerts).set(patch).where(eq(alerts.id, id)).returning().get() : alert
  addAlertEvent(id, actor, eventType, note)
  writeAudit(actor, `alert.${eventType}`, 'alert', id, { ref: alert.ref, ...(note ? { note: note.slice(0, 200) } : {}) }, event)
  emitAlertUpdate(updated)
  return updated
})
