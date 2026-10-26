import { sql } from 'drizzle-orm'
import { z } from 'zod'
import { EVALUATOR_CATALOGUE } from '../../../ccm/catalogue'

const body = z.object({
  name: z.string().trim().min(3).max(120),
  description: z.string().trim().min(3).max(500),
  evaluator: z.string().refine(e => e in EVALUATOR_CATALOGUE, 'Unknown evaluator'),
  params: z.record(z.string(), z.number()),
  severity: z.enum(['critical', 'high', 'medium', 'low']),
  sla: slaSchema,
  escalationPath: escalationSchema,
  module: z.string().trim().min(2).max(80),
  dataSource: z.string().trim().min(2).max(120),
})

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'ccm:manage')
  const input = await validateBody(event, body)
  validateParams(input.evaluator, input.params)
  const db = useDb()
  const max = db.select({ id: sql<string>`max(${tables.ccmRules.id})` }).from(tables.ccmRules).get()?.id ?? 'CCM-000'
  const id = `CCM-${String(Number(max.slice(4)) + 1).padStart(3, '0')}`
  const rule = db.insert(tables.ccmRules).values({ id, ...input }).returning().get()
  writeAudit(actorOf(user), 'ccm.rule_created', 'ccm_rule', id, { ...input }, event)
  runCcmCycle()
  return rule
})
