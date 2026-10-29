import { describe, expect, it } from 'vitest'
import { bandFor, residualFor, scoreRisk } from '../shared/utils/risk'
import { addBusinessDays, computeDueAt, escalationStepMs } from '../shared/utils/sla'
import { countryRiskOf, ratingForScore, scoreAml } from '../shared/utils/aml'
import { classifySupplier } from '../shared/utils/tprm'
import { can, ROLES } from '../shared/utils/rbac'

describe('risk scoring (§4.3)', () => {
  it('bands scores on the 5×5 matrix', () => {
    expect(bandFor(4)).toBe('low')
    expect(bandFor(5)).toBe('moderate')
    expect(bandFor(10)).toBe('high')
    expect(bandFor(15)).toBe('critical')
    expect(bandFor(25)).toBe('critical')
  })

  it('leaves residual equal to inherent when no controls are mapped', () => {
    const s = scoreRisk(5, 4, [])
    expect(s.inherent.score).toBe(20)
    expect(s.residual.score).toBe(20)
    expect(s.controlGap).toBe(true)
  })

  it('reduces likelihood by combined control effectiveness, never below 1', () => {
    expect(residualFor(5, 4, ['effective']).likelihood).toBe(2) // 4 × 0.4 = 1.6 → 2
    expect(residualFor(5, 4, ['ineffective', 'not_tested']).score).toBe(20)
    expect(residualFor(5, 1, ['effective', 'effective']).likelihood).toBe(1)
  })

  it('caps combined reduction at 80%', () => {
    expect(residualFor(5, 5, ['effective', 'effective', 'effective', 'effective']).reduction).toBe(0.8)
  })
})

describe('SLA computation (§5)', () => {
  it('skips weekends for business-day SLAs', () => {
    const friday = new Date('2026-10-16T10:00:00')
    expect(addBusinessDays(friday, 1).getDay()).toBe(1) // Monday
    expect(addBusinessDays(friday, 5).toDateString()).toBe(new Date('2026-10-23T10:00:00').toDateString())
  })

  it('computes T-1 relative to the entity deadline but never in the past', () => {
    const detected = new Date('2026-10-16T09:00:00')
    const due = computeDueAt({ type: 'before_entity_due', minutes: 1440 }, detected, new Date('2026-10-19T09:00:00'))
    expect(due.toISOString()).toBe(new Date('2026-10-18T09:00:00').toISOString())
    const late = computeDueAt({ type: 'before_entity_due', minutes: 1440 }, detected, new Date('2026-10-16T12:00:00'))
    expect(late.getTime()).toBe(detected.getTime())
  })

  it('treats "Immediate" as already due', () => {
    const d = new Date()
    expect(computeDueAt({ type: 'immediate' }, d).getTime()).toBe(d.getTime())
  })

  it('never escalates faster than every 30 minutes', () => {
    expect(escalationStepMs({ type: 'minutes', minutes: 5 })).toBe(30 * 60_000)
  })
})

describe('AML scoring (§4.5)', () => {
  it('explains every point of the score', () => {
    const s = scoreAml({ countryRisk: 'high', counterpartyCountryRisk: 'high', riskRating: 'high', pep: true, adverseMedia: false, sanctionsStatus: 'clear', amount: 340_000 })
    expect(s.score).toBe(s.factors.reduce((a, f) => a + f.weight, 0))
    expect(s.factors[0]!.weight).toBeGreaterThanOrEqual(s.factors.at(-1)!.weight)
  })
  it('triggers EDD rating for high scores', () => {
    expect(ratingForScore(60)).toBe('high')
    expect(ratingForScore(25)).toBe('enhanced')
    expect(ratingForScore(5)).toBe('standard')
  })
  it('classifies jurisdictions', () => {
    expect(countryRiskOf('VG')).toBe('high')
    expect(countryRiskOf('AE')).toBe('medium')
    expect(countryRiskOf('GB')).toBe('low')
  })
})

describe('supplier classification (§4.7)', () => {
  it('makes a data-processing, high-spend, regulated supplier critical', () => {
    expect(classifySupplier({ dataAccess: true, annualSpend: 2_000_000, countryRisk: 'low', questionnaireScore: 80, regulatoryCritical: true })).toBe('critical')
  })
  it('keeps a small, no-data supplier low', () => {
    expect(classifySupplier({ dataAccess: false, annualSpend: 10_000, countryRisk: 'low', questionnaireScore: 90, regulatoryCritical: false })).toBe('low')
  })
})

describe('RBAC (§6.5)', () => {
  it('reserves SAR submission for the MLRO', () => {
    expect(can('MLRO', 'sar:submit')).toBe(true)
    expect(can('AML_ANALYST', 'sar:submit')).toBe(false)
    expect(can('CRO', 'sar:submit')).toBe(false)
  })
  it('enforces documented restrictions', () => {
    expect(can('AUDIT', 'aml:read')).toBe(false)     // Head of Audit: no AML case details
    expect(can('AUDIT', 'hr:read')).toBe(false)      // … or HR vetting data
    expect(can('MLRO', 'hr:read')).toBe(false)       // MLRO: no HR data
    expect(can('CISO', 'regreport:submit')).toBe(false)
    expect(can('HR', 'risk:read')).toBe(false)
    expect(can('ADMIN', 'pii:view')).toBe(false)     // Admin sees PII masked
    expect(ROLES.BU_MANAGER.scope).toBe('bu')
  })
  it('gives the CRO full read access', () => {
    for (const p of ROLES.CRO.permissions) expect(can('CRO', p)).toBe(true)
    expect(ROLES.CRO.permissions.filter(p => p.endsWith(':read')).length).toBeGreaterThan(15)
  })
})
