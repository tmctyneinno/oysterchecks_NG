import { inArray, sql } from 'drizzle-orm'
import { EVALUATOR_CATALOGUE } from '../../../ccm/catalogue'

export default defineEventHandler(async (event) => {
  await requirePermission(event, 'ccm:read')
  const db = useDb()
  const { ccmRules, alerts } = tables
  const rules = db.select().from(ccmRules).orderBy(ccmRules.id).all()
  const open = db.select({ ruleId: alerts.ruleId, n: sql<number>`count(*)` }).from(alerts)
    .where(inArray(alerts.status, [...OPEN_STATUSES])).groupBy(alerts.ruleId).all()
  const openByRule = new Map(open.map(o => [o.ruleId, o.n]))
  return {
    rules: rules.map(r => ({
      ...r,
      slaLabel: slaLabel(r.sla),
      escalationLabel: escalationPathLabel(r.escalationPath),
      openAlerts: openByRule.get(r.id) ?? 0,
      evaluatorLabel: EVALUATOR_CATALOGUE[r.evaluator]?.label ?? r.evaluator,
    })),
    catalogue: EVALUATOR_CATALOGUE,
    targets: Object.entries(ESCALATION_TARGETS).map(([key, v]) => ({ key, label: v.label })),
  }
})
