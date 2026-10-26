// AML risk scoring (§4.5 step 3). Deliberately a transparent, weighted-factor model: every score is
// returned with the factors that produced it, so analysts and regulators can see why (UK GDPR
// Art. 22 safeguards, §8.2). A trained model can replace `scoreAml` behind the same contract.

export interface AmlScoreInput {
  countryRisk: 'low' | 'medium' | 'high'
  counterpartyCountryRisk?: 'low' | 'medium' | 'high'
  riskRating: 'standard' | 'enhanced' | 'high'
  pep: boolean
  adverseMedia: boolean
  sanctionsStatus: 'pending' | 'clear' | 'potential_match' | 'confirmed_match'
  amount?: number
  volumeZScore?: number
}

export const HIGH_RISK_COUNTRIES = new Set(['VG', 'PA', 'KY', 'IR', 'KP', 'MM', 'SY', 'RU', 'BY'])
export const MEDIUM_RISK_COUNTRIES = new Set(['AE', 'NG', 'CY', 'TR', 'ZA', 'IN', 'MT'])
export const countryRiskOf = (code: string): 'low' | 'medium' | 'high' =>
  HIGH_RISK_COUNTRIES.has(code) ? 'high' : MEDIUM_RISK_COUNTRIES.has(code) ? 'medium' : 'low'

export function scoreAml(i: AmlScoreInput) {
  const factors: { factor: string, weight: number }[] = []
  const add = (cond: boolean, factor: string, weight: number) => { if (cond) factors.push({ factor, weight }) }

  add(i.sanctionsStatus === 'confirmed_match', 'Confirmed sanctions match', 60)
  add(i.sanctionsStatus === 'potential_match', 'Potential sanctions / PEP list match', 30)
  add(i.pep, 'Politically exposed person', 15)
  add(i.adverseMedia, 'Adverse media', 10)
  add(i.riskRating === 'high', 'High customer risk rating', 15)
  add(i.riskRating === 'enhanced', 'Enhanced customer risk rating', 8)
  add(i.countryRisk === 'high', 'High-risk customer jurisdiction', 12)
  add(i.countryRisk === 'medium', 'Medium-risk customer jurisdiction', 5)
  add(i.counterpartyCountryRisk === 'high', 'High-risk counterparty jurisdiction', 18)
  add(i.counterpartyCountryRisk === 'medium', 'Medium-risk counterparty jurisdiction', 6)
  add((i.amount ?? 0) >= 100_000, 'Transaction ≥ £100k', 12)
  add((i.amount ?? 0) >= 10_000 && (i.amount ?? 0) < 100_000, 'Transaction ≥ £10k', 5)
  add((i.volumeZScore ?? 0) > 3, `Volume ${(i.volumeZScore ?? 0).toFixed(1)}σ above baseline`, 20)

  const score = Math.min(100, factors.reduce((s, f) => s + f.weight, 0))
  return { score, factors: factors.sort((a, b) => b.weight - a.weight) }
}

/** §4.5 step 3: Standard / Enhanced / High, with EDD triggered for High. */
export const ratingForScore = (score: number) => score >= 45 ? 'high' as const : score >= 20 ? 'enhanced' as const : 'standard' as const

/** §4.5 step 8: periodic KYC refresh schedule in months. */
export const KYC_REFRESH_MONTHS = { standard: 36, enhanced: 12, high: 6 } as const
