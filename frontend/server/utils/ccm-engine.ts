import { eq } from 'drizzle-orm'
import { evaluateKris, evaluateOverdueActions, evaluators } from '../ccm/evaluators'

let running = false

/**
 * One CCM cycle (§4.4 steps 2–5): evaluate every enabled rule against current source data,
 * raise alerts for new breaches, update rule LED state, evaluate KRIs, then run escalation.
 */
export function runCcmCycle(now = new Date()) {
  if (running) return null
  running = true
  const started = Date.now()
  try {
    const db = useDb()
    const { ccmRules } = tables
    let alertsCreated = 0
    const rules = db.select().from(ccmRules).where(eq(ccmRules.enabled, true)).all()

    for (const rule of rules) {
      const evaluate = evaluators[rule.evaluator]
      if (!evaluate) {
        db.update(ccmRules).set({ state: 'unknown', lastRunAt: now }).where(eq(ccmRules.id, rule.id)).run()
        continue
      }
      try {
        const { breaches, warnings } = evaluate(db, now, rule.params)
        for (const b of breaches) {
          const created = raiseFromBreach({ source: 'ccm', ruleId: rule.id, module: rule.module, severity: rule.severity, sla: rule.sla, escalationPath: rule.escalationPath }, rule.id, b)
          if (created) alertsCreated++
        }
        const state = breaches.length ? 'fail' : warnings ? 'warn' : 'pass'
        db.update(ccmRules).set({ state, lastRunAt: now, lastBreaches: breaches.length, lastWarnings: warnings }).where(eq(ccmRules.id, rule.id)).run()
      }
      catch (err) {
        console.error(`[ccm] ${rule.id} evaluation failed`, err)
        db.update(ccmRules).set({ state: 'unknown', lastRunAt: now }).where(eq(ccmRules.id, rule.id)).run()
      }
    }

    const kri = evaluateKris(db)
    for (const b of kri.breaches) {
      const created = raiseFromBreach({
        source: 'kri', module: 'Enterprise Risk', severity: 'high',
        sla: { type: 'business_days', days: 1 }, escalationPath: ['CONTROL_OWNER', 'CRO'],
      }, 'KRI', b)
      if (created) alertsCreated++
    }

    for (const b of evaluateOverdueActions(db, now)) {
      const created = raiseFromBreach({
        source: 'ccm', module: 'Issue Management', severity: 'medium',
        sla: { type: 'business_days', days: 2 }, escalationPath: ['CONTROL_OWNER', 'CCO'],
      }, 'ACTION', b)
      if (created) alertsCreated++
    }

    const { escalated } = runEscalation(now)
    const summary = { rulesEvaluated: rules.length, alertsCreated, escalated, durationMs: Date.now() - started, at: now.toISOString() }
    publish({ type: 'ccm', payload: summary })
    return summary
  }
  finally {
    running = false
  }
}
