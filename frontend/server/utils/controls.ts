import { desc, eq } from 'drizzle-orm'

/**
 * Control effectiveness is derived from test evidence, never self-reported (§1.2 "Independent
 * data-driven assurance"): last three tests all pass → effective; latest two fail → ineffective;
 * otherwise partially effective. CSA results are shown alongside but do not override this.
 */
export function recomputeEffectiveness(controlId: number) {
  const db = useDb()
  const { controls, controlTests } = tables
  const recent = db.select({ result: controlTests.result, at: controlTests.at }).from(controlTests)
    .where(eq(controlTests.controlId, controlId)).orderBy(desc(controlTests.at)).limit(3).all()
  if (!recent.length) return 'not_tested' as const
  const effectiveness: ControlEffectiveness
    = recent.every(r => r.result === 'pass') ? 'effective'
      : recent.length >= 2 && recent[0]!.result === 'fail' && recent[1]!.result === 'fail' ? 'ineffective'
        : 'partially_effective'
  db.update(controls).set({ effectiveness, lastTestedAt: recent[0]!.at }).where(eq(controls.id, controlId)).run()
  return effectiveness
}
