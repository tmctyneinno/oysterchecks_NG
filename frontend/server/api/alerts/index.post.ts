import { z } from 'zod'

const body = z.object({
  title: z.string().trim().min(5).max(200),
  detail: z.string().trim().max(4000).optional(),
  module: z.string().trim().min(2).max(80),
  severity: z.enum(['critical', 'high', 'medium', 'low']),
  businessUnit: z.string().nullable().optional(),
  slaHours: z.number().min(0.25).max(24 * 30),
  escalationPath: z.array(z.enum(Object.keys(ESCALATION_TARGETS) as [EscalationTarget, ...EscalationTarget[]])).min(1),
})

/** Manual alert raised by a user (e.g. issue spotted outside automated monitoring). */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'alerts:act')
  const input = await validateBody(event, body)
  const businessUnit = ROLES[user.role].scope === 'bu' ? user.businessUnit : input.businessUnit ?? null
  return createAlert({
    source: 'manual',
    title: input.title,
    detail: input.detail,
    module: input.module,
    severity: input.severity,
    businessUnit,
    sla: { type: 'minutes', minutes: Math.round(input.slaHours * 60) },
    escalationPath: input.escalationPath,
  }, actorOf(user))
})
