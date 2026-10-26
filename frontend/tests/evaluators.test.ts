// Runs every CCM evaluator against the seeded demo dataset in an in-memory database and checks
// that each rule finds exactly the breaches the seed was designed to contain.
import { beforeAll, describe, expect, it } from 'vitest'
import Database from 'better-sqlite3'
import { drizzle } from 'drizzle-orm/better-sqlite3'
import { migrate } from 'drizzle-orm/better-sqlite3/migrator'
import { eq } from 'drizzle-orm'
import * as schema from '../server/database/schema'
import { DEFAULT_RULES, seedDatabase } from '../server/database/seed'
import { evaluateKris, evaluateOverdueActions, evaluators } from '../server/ccm/evaluators'

const now = new Date('2026-10-16T12:00:00Z')
const db = drizzle(new Database(':memory:'), { schema })

beforeAll(() => {
  migrate(db, { migrationsFolder: 'server/database/migrations' })
  seedDatabase(db, 'not-a-real-hash', now)
})

const run = (id: string) => {
  const rule = DEFAULT_RULES.find(r => r.id === id)!
  return evaluators[rule.evaluator]!(db, now, rule.params)
}

describe('CCM evaluators against seeded data', () => {
  it('every default rule maps to an implemented evaluator', () => {
    for (const r of DEFAULT_RULES) expect(evaluators[r.evaluator], r.id).toBeTypeOf('function')
  })

  it('CCM-001 flags customers unverified after 24h, warns on newer ones', () => {
    const r = run('CCM-001')
    expect(r.breaches).toHaveLength(2)
    expect(r.warnings).toBe(1)
  })

  it('CCM-002 flags the unscreened £340k wire', () => {
    const r = run('CCM-002')
    expect(r.breaches).toHaveLength(1)
    expect(r.breaches[0]!.detail).toContain('£340,000')
  })

  it('CCM-003 flags SAR decisions older than 7 days', () => {
    const r = run('CCM-003')
    expect(r.breaches).toHaveLength(1)
    expect(r.warnings).toBe(1)
  })

  it('CCM-004 passes when every high-risk customer has completed EDD', () => {
    expect(run('CCM-004')).toEqual({ breaches: [], warnings: 0 })
  })

  it('CCM-005 applies the risk-based refresh schedule', () => {
    expect(run('CCM-005').breaches).toHaveLength(0)
    const nana = db.select().from(schema.customers).where(eq(schema.customers.name, 'Nana Osei-Bonsu')).get()!
    db.update(schema.customers).set({ kycVerifiedAt: new Date(now.getTime() - 400 * 86_400_000) }).where(eq(schema.customers.id, nana.id)).run()
    const r = run('CCM-005') // enhanced-risk schedule is 12 months
    expect(r.breaches.map(b => b.entityId)).toEqual([nana.id])
  })

  it('CCM-006 breaches only inside the 3-day window', () => {
    expect(run('CCM-006').breaches).toHaveLength(0)
    const chloe = db.select().from(schema.employees).where(eq(schema.employees.name, 'Chloe Bennett')).get()!
    db.update(schema.employees).set({ startDate: new Date(now.getTime() + 2 * 86_400_000) }).where(eq(schema.employees.id, chloe.id)).run()
    expect(run('CCM-006').breaches).toHaveLength(1)
  })

  it('CCM-007 flags privileged accounts unreviewed for 90+ days', () => {
    expect(run('CCM-007').breaches).toHaveLength(0)
    const acct = db.select().from(schema.accessAccounts).where(eq(schema.accessAccounts.account, 'adm-jhussein')).get()!
    db.update(schema.accessAccounts).set({ lastReviewedAt: new Date(now.getTime() - 120 * 86_400_000) }).where(eq(schema.accessAccounts.id, acct.id)).run()
    expect(run('CCM-007').breaches).toHaveLength(1)
  })

  it('CCM-008 breaches when a return is within T-3 days', () => {
    expect(run('CCM-008').breaches).toHaveLength(0)
    const report = db.select().from(schema.regulatoryReports).where(eq(schema.regulatoryReports.name, 'FSA001 Balance Sheet')).get()!
    db.update(schema.regulatoryReports).set({ dueAt: new Date(now.getTime() + 2 * 86_400_000) }).where(eq(schema.regulatoryReports.id, report.id)).run()
    const r = run('CCM-008')
    expect(r.breaches).toHaveLength(1)
    expect(r.breaches[0]!.entityDueAt).toBeInstanceOf(Date)
  })

  it('CCM-009 flags overdue control self-assessments', () => {
    expect(run('CCM-009').breaches).toHaveLength(0)
    const pending = db.select().from(schema.csaAssessments).all().find(c => !c.submittedAt)!
    db.update(schema.csaAssessments).set({ dueAt: new Date(now.getTime() - 86_400_000) }).where(eq(schema.csaAssessments.id, pending.id)).run()
    expect(run('CCM-009').breaches).toHaveLength(1)
  })

  it('CCM-010 flags the overdue audit recommendation', () => {
    const r = run('CCM-010')
    expect(r.breaches).toHaveLength(1)
    expect(r.breaches[0]!.title).toContain('F-2026-001')
  })

  it('CCM-011 detects the >3σ volume spike', () => {
    const r = run('CCM-011')
    expect(r.breaches).toHaveLength(1)
    expect(r.breaches[0]!.detail).toMatch(/σ above/)
  })

  it('CCM-012 flags controls whose last two tests failed', () => {
    const r = run('CCM-012')
    expect(r.breaches.map(b => b.title).sort()).toEqual([
      'Control failed two consecutive tests: C-007',
      'Control failed two consecutive tests: C-009',
    ])
  })

  it('produces stable dedupe keys so repeated cycles do not duplicate alerts', () => {
    const a = run('CCM-001').breaches.map(b => b.key)
    const b = run('CCM-001').breaches.map(b => b.key)
    expect(a).toEqual(b)
  })

  it('evaluates KRIs and overdue corrective actions', () => {
    expect(evaluateKris(db).breaches.length).toBeGreaterThanOrEqual(1)
    expect(evaluateOverdueActions(db, now).map(b => b.title)).toEqual(['Corrective action overdue: ACT-0002'])
  })
})
