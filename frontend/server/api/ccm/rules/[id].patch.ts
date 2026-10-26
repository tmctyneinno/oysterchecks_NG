import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  name: z.string().trim().min(3).max(120).optional(),
  description: z.string().trim().min(3).max(500).optional(),
  params: z.record(z.string(), z.number()).optional(),
  severity: z.enum(['critical', 'high', 'medium', 'low']).optional(),
  sla: slaSchema.optional(),
  escalationPath: escalationSchema.optional(),
  enabled: z.boolean().optional(),
  reason: z.string({ error: 'Give a reason for the change (recorded in the audit log)' }).trim().min(5, 'Give a reason for the change (recorded in the audit log)').max(500),
})

/** Rule changes are a control override (§6.5), restricted to ccm:manage, and the before/after is audited. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'ccm:manage')
  const id = getRouterParam(event, 'id')!
  const { reason, ...patch } = await validateBody(event, body)
  const { ccmRules } = tables
  const db = useDb()
  const rule = db.select().from(ccmRules).where(eq(ccmRules.id, id)).get()
  if (!rule) notFound('Rule')
  if (patch.params) validateParams(rule.evaluator, { ...rule.params, ...patch.params })

  const updated = db.update(ccmRules).set({ ...patch, params: patch.params ? { ...rule.params, ...patch.params } : undefined, state: patch.enabled === false ? 'unknown' : rule.state })
    .where(eq(ccmRules.id, id)).returning().get()
  const changed = Object.fromEntries(Object.keys(patch).map(k => [k, { from: (rule as any)[k], to: (updated as any)[k] }]))
  writeAudit(actorOf(user), 'ccm.rule_changed', 'ccm_rule', id, { reason, changed }, event)
  publish({ type: 'data', payload: { domain: 'ccm' } })
  return updated
})
