// Third-party risk classification (§4.7 step 3) and review cadence (§4.7 step 6).
export type SupplierTier = 'critical' | 'high' | 'medium' | 'low'

export interface SupplierRiskInput {
  dataAccess: boolean
  annualSpend: number
  countryRisk: 'low' | 'medium' | 'high'
  questionnaireScore: number | null
  regulatoryCritical: boolean
}

/** Tier from data access, financial exposure, regulatory criticality and geography. */
export function classifySupplier(i: SupplierRiskInput): SupplierTier {
  let points = 0
  if (i.dataAccess) points += 3
  if (i.annualSpend >= 1_000_000) points += 3
  else if (i.annualSpend >= 250_000) points += 2
  else if (i.annualSpend >= 50_000) points += 1
  if (i.regulatoryCritical) points += 3
  if (i.countryRisk === 'high') points += 3
  else if (i.countryRisk === 'medium') points += 1
  if (i.questionnaireScore !== null && i.questionnaireScore < 60) points += 2
  return points >= 8 ? 'critical' : points >= 5 ? 'high' : points >= 3 ? 'medium' : 'low'
}

/** Review cadence in months. The spec gives critical/high/medium; low (36m) is our assumption. */
export const SUPPLIER_REVIEW_MONTHS: Record<SupplierTier, number> = { critical: 12, high: 18, medium: 24, low: 36 }
