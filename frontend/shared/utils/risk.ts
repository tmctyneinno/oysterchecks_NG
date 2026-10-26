// Risk scoring, spec §4.3. Inherent = Impact × Likelihood on a 5×5 matrix; residual is the
// inherent position adjusted by the effectiveness of mapped controls.

export type ControlEffectiveness = 'effective' | 'partially_effective' | 'ineffective' | 'not_tested'
export type RiskBand = 'low' | 'moderate' | 'high' | 'critical'

export const EFFECTIVENESS_REDUCTION: Record<ControlEffectiveness, number> = {
  effective: 0.6,
  partially_effective: 0.3,
  ineffective: 0,
  not_tested: 0,
}

/** Cap so that no stack of controls ever claims a risk is fully eliminated. */
const MAX_REDUCTION = 0.8

export const bandFor = (score: number): RiskBand =>
  score >= 15 ? 'critical' : score >= 10 ? 'high' : score >= 5 ? 'moderate' : 'low'

/**
 * Controls are treated as independent layers: combined reduction = 1 − Π(1 − rᵢ).
 * The reduction is applied to likelihood (controls mainly make events less likely), keeping
 * impact unchanged so the residual still plots on the same 5×5 matrix.
 */
export function residualFor(impact: number, likelihood: number, controls: ControlEffectiveness[]) {
  const combined = Math.min(MAX_REDUCTION, 1 - controls.reduce((acc, c) => acc * (1 - EFFECTIVENESS_REDUCTION[c]), 1))
  const residualLikelihood = Math.max(1, Math.round(likelihood * (1 - combined)))
  const score = impact * residualLikelihood
  return { impact, likelihood: residualLikelihood, score, band: bandFor(score), reduction: combined }
}

export function scoreRisk(impact: number, likelihood: number, controls: ControlEffectiveness[]) {
  const inherentScore = impact * likelihood
  return {
    inherent: { impact, likelihood, score: inherentScore, band: bandFor(inherentScore) },
    residual: residualFor(impact, likelihood, controls),
    controlGap: controls.length === 0,
  }
}
