// CCM rule evaluators, spec §5. Each evaluator is a pure function of (database, clock, params) and
// returns the breaches it finds plus a count of near-threshold items (which drive the amber LED).
// Evaluators read source-system data directly; nothing here relies on self-reported status.
import { and, desc, eq, gte, inArray, isNull, lte, ne } from 'drizzle-orm'
import type { BetterSQLite3Database } from 'drizzle-orm/better-sqlite3'
import * as t from '../database/schema'

type Db = BetterSQLite3Database<typeof t>

export interface Breach {
  /** Stable per breached entity so the same breach never raises two open alerts. */
  key: string
  entityType: string
  entityId: number
  title: string
  detail: string
  businessUnit?: string | null
  ownerUserId?: number | null
  entityDueAt?: Date | null
}
export interface EvalResult { breaches: Breach[], warnings: number }
export type Evaluator = (db: Db, now: Date, params: Record<string, number>) => EvalResult

const DAY = 86_400_000
const HOUR = 3_600_000
const ago = (now: Date, ms: number) => new Date(now.getTime() - ms)
const ahead = (now: Date, ms: number) => new Date(now.getTime() + ms)
const gbp = (n: number) => `£${Math.round(n).toLocaleString('en-GB')}`

export const evaluators: Record<string, Evaluator> = {
  // CCM-001: customer record created; no verified KYC after N hours
  unverified_onboarding(db, now, { hours = 24 }) {
    const rows = db.select().from(t.customers).where(ne(t.customers.kycStatus, 'verified')).all()
    const cutoff = ago(now, hours * HOUR)
    const breaches = rows.filter(c => c.createdAt <= cutoff).map(c => ({
      key: `customer:${c.id}`, entityType: 'customer', entityId: c.id, businessUnit: c.businessUnit,
      title: `Unverified onboarding: ${c.ref}`,
      detail: `${c.type === 'entity' ? 'Entity' : 'Customer'} created ${Math.floor((now.getTime() - c.createdAt.getTime()) / HOUR)}h ago; KYC status "${c.kycStatus}".`,
    }))
    return { breaches, warnings: rows.length - breaches.length }
  },

  // CCM-002: transaction above threshold with no sanctions screening result
  unscreened_high_value(db, now, { threshold = 10_000, graceMinutes = 5 }) {
    const rows = db.select().from(t.transactions)
      .where(and(eq(t.transactions.screeningStatus, 'pending'), gte(t.transactions.amount, threshold))).all()
    const cutoff = ago(now, graceMinutes * 60_000)
    const breaches = rows.filter(x => x.at <= cutoff).map(x => ({
      key: `transaction:${x.id}`, entityType: 'transaction', entityId: x.id,
      title: `Unscreened high-value transaction: ${x.ref}`,
      detail: `${gbp(x.amount)} ${x.direction === 'out' ? 'to' : 'from'} ${x.counterparty} (${x.counterpartyCountry}); no sanctions screening result after ${graceMinutes} minutes.`,
    }))
    return { breaches, warnings: rows.length - breaches.length }
  },

  // CCM-003: SAR decided but not submitted to the NCA within N days (§4.5 step 7)
  sar_overdue(db, now, { days = 7, warnDays = 5 }) {
    const rows = db.select().from(t.amlCases).where(eq(t.amlCases.status, 'sar_decided')).all()
    const breaches: Breach[] = []
    let warnings = 0
    for (const c of rows) {
      const age = now.getTime() - (c.sarDecisionAt ?? c.openedAt).getTime()
      if (age > days * DAY) {
        breaches.push({
          key: `aml_case:${c.id}`, entityType: 'aml_case', entityId: c.id, ownerUserId: c.analystId,
          title: `SAR overdue: ${c.ref}`,
          detail: `SAR decision recorded ${Math.floor(age / DAY)} days ago; no NCA submission confirmed.`,
        })
      }
      else if (age > warnDays * DAY) warnings++
    }
    return { breaches, warnings }
  },

  // CCM-004: high-risk customer without completed EDD within N days
  edd_incomplete(db, now, { days = 10 }) {
    const rows = db.select().from(t.customers)
      .where(and(eq(t.customers.riskRating, 'high'), inArray(t.customers.eddStatus, ['required', 'in_progress']))).all()
    const cutoff = ago(now, days * DAY)
    const breaches = rows.filter(c => c.createdAt <= cutoff).map(c => ({
      key: `customer:${c.id}`, entityType: 'customer', entityId: c.id, businessUnit: c.businessUnit,
      title: `EDD not completed: ${c.ref}`,
      detail: `High-risk customer; EDD status "${c.eddStatus}" beyond the ${days}-day limit.`,
    }))
    return { breaches, warnings: rows.length - breaches.length }
  },

  // CCM-005: KYC older than the risk-based refresh schedule (standard 36m / enhanced 12m / high 6m)
  kyc_expired(db, now, { standardMonths = 36, enhancedMonths = 12, highMonths = 6, warnDays = 30 }) {
    const months = { standard: standardMonths, enhanced: enhancedMonths, high: highMonths }
    const rows = db.select().from(t.customers).where(eq(t.customers.kycStatus, 'verified')).all()
    const breaches: Breach[] = []
    let warnings = 0
    for (const c of rows) {
      if (!c.kycVerifiedAt) continue
      const expires = new Date(c.kycVerifiedAt)
      expires.setMonth(expires.getMonth() + months[c.riskRating])
      if (expires <= now) {
        breaches.push({
          key: `customer:${c.id}:${expires.toISOString().slice(0, 10)}`, entityType: 'customer', entityId: c.id, businessUnit: c.businessUnit,
          title: `KYC refresh overdue: ${c.ref}`,
          detail: `${c.riskRating[0]!.toUpperCase() + c.riskRating.slice(1)}-risk schedule (${months[c.riskRating]} months); KYC expired ${expires.toISOString().slice(0, 10)}.`,
        })
      }
      else if (expires <= ahead(now, warnDays * DAY)) warnings++
    }
    return { breaches, warnings }
  },

  // CCM-006: new starter within N days of start date without cleared vetting
  new_starter_vetting(db, now, { days = 3, warnDays = 10 }) {
    const rows = db.select().from(t.employees)
      .where(and(ne(t.employees.vettingStatus, 'cleared'), isNull(t.employees.terminatedAt), lte(t.employees.startDate, ahead(now, warnDays * DAY)))).all()
    const breaches = rows.filter(e => e.startDate <= ahead(now, days * DAY)).map(e => ({
      key: `employee:${e.id}`, entityType: 'employee', entityId: e.id, businessUnit: e.businessUnit,
      title: `New starter without vetting clearance: ${e.ref}`,
      detail: `${e.jobTitle}, ${e.department}; starts ${e.startDate.toISOString().slice(0, 10)}; ${e.vettingType} status "${e.vettingStatus}".`,
    }))
    return { breaches, warnings: rows.length - breaches.length }
  },

  // CCM-007: privileged access not reviewed for more than N days
  privileged_access_review(db, now, { days = 90, warnDays = 14 }) {
    const rows = db.select().from(t.accessAccounts).where(eq(t.accessAccounts.privileged, true)).all()
    const cutoff = ago(now, days * DAY)
    const breaches: Breach[] = []
    let warnings = 0
    for (const a of rows) {
      if (!a.lastReviewedAt || a.lastReviewedAt <= cutoff) {
        breaches.push({
          key: `access:${a.id}:${a.lastReviewedAt?.getTime() ?? 0}`, entityType: 'access_account', entityId: a.id,
          title: `Privileged access not reviewed: ${a.account}`,
          detail: `${a.system}: ${a.groups.join(', ')}. Last review ${a.lastReviewedAt ? a.lastReviewedAt.toISOString().slice(0, 10) : 'never'}.`,
        })
      }
      else if (a.lastReviewedAt <= ago(now, (days - warnDays) * DAY)) warnings++
    }
    return { breaches, warnings }
  },

  // CCM-008: regulatory report due within T-N days and not submitted
  reg_report_deadline(db, now, { days = 3, warnDays = 7 }) {
    const rows = db.select().from(t.regulatoryReports)
      .where(and(isNull(t.regulatoryReports.submittedAt), lte(t.regulatoryReports.dueAt, ahead(now, warnDays * DAY)))).all()
    const breaches = rows.filter(r => r.dueAt <= ahead(now, days * DAY)).map(r => ({
      key: `report:${r.id}:${r.dueAt.getTime()}`, entityType: 'regulatory_report', entityId: r.id, ownerUserId: r.ownerId, entityDueAt: r.dueAt,
      title: `Regulatory report approaching deadline: ${r.name}`,
      detail: `${r.regulator} ${r.frequency.toLowerCase()} return due ${r.dueAt.toISOString().slice(0, 10)}; submission not confirmed.`,
    }))
    return { breaches, warnings: rows.length - breaches.length }
  },

  // CCM-009: first-line control self-assessment not completed by due date
  csa_overdue(db, now, { warnDays = 3 }) {
    const rows = db.select({ csa: t.csaAssessments, control: t.controls }).from(t.csaAssessments)
      .innerJoin(t.controls, eq(t.controls.id, t.csaAssessments.controlId))
      .where(and(isNull(t.csaAssessments.submittedAt), lte(t.csaAssessments.dueAt, ahead(now, warnDays * DAY)))).all()
    const breaches = rows.filter(r => r.csa.dueAt < now).map(({ csa, control }) => ({
      key: `csa:${csa.id}`, entityType: 'csa', entityId: csa.id, businessUnit: csa.businessUnit,
      title: `CSA overdue: ${control.ref} (${csa.businessUnit})`,
      detail: `${csa.period} self-assessment of "${control.name}" was due ${csa.dueAt.toISOString().slice(0, 10)}.`,
    }))
    return { breaches, warnings: rows.length - breaches.length }
  },

  // CCM-010: audit recommendation past due date with no closure evidence
  audit_rec_overdue(db, now, { warnDays = 7 }) {
    const rows = db.select().from(t.auditFindings)
      .where(and(eq(t.auditFindings.status, 'open'), lte(t.auditFindings.dueAt, ahead(now, warnDays * DAY)))).all()
    const breaches = rows.filter(f => f.dueAt < now).map(f => ({
      key: `finding:${f.id}`, entityType: 'audit_finding', entityId: f.id, businessUnit: f.businessUnit, ownerUserId: f.ownerId,
      title: `Audit recommendation overdue: ${f.ref}`,
      detail: `${f.rating.toUpperCase()}-rated: "${f.title}". Due ${f.dueAt.toISOString().slice(0, 10)}; no closure evidence.`,
    }))
    return { breaches, warnings: rows.length - breaches.length }
  },

  // CCM-011: customer 24h volume more than N standard deviations above the 90-day daily average
  anomalous_volume(db, now, { sigma = 3, baselineDays = 90, minBaselineDays = 10 }) {
    const since = ago(now, (baselineDays + 1) * DAY)
    const rows = db.select({ customerId: t.transactions.customerId, amount: t.transactions.amount, at: t.transactions.at })
      .from(t.transactions).where(gte(t.transactions.at, since)).all()
    const recentCutoff = ago(now, DAY)
    const byCustomer = new Map<number, { recent: number, days: Map<number, number> }>()
    for (const r of rows) {
      const entry = byCustomer.get(r.customerId) ?? { recent: 0, days: new Map() }
      if (r.at > recentCutoff) entry.recent += r.amount
      else {
        const dayIdx = Math.floor((now.getTime() - r.at.getTime()) / DAY)
        entry.days.set(dayIdx, (entry.days.get(dayIdx) ?? 0) + r.amount)
      }
      byCustomer.set(r.customerId, entry)
    }
    const breaches: Breach[] = []
    let warnings = 0
    const dayStamp = now.toISOString().slice(0, 10)
    const refs = new Map(db.select({ id: t.customers.id, ref: t.customers.ref, bu: t.customers.businessUnit }).from(t.customers).all().map(c => [c.id, c]))
    for (const [customerId, { recent, days }] of byCustomer) {
      if (days.size < minBaselineDays || recent === 0) continue
      // Days with no activity count as zero volume in the baseline.
      const series = Array.from({ length: baselineDays }, (_, i) => days.get(i + 1) ?? 0)
      const mean = series.reduce((a, b) => a + b, 0) / series.length
      const sd = Math.sqrt(series.reduce((a, b) => a + (b - mean) ** 2, 0) / series.length)
      const z = sd > 0 ? (recent - mean) / sd : 0
      if (z > sigma) {
        breaches.push({
          key: `customer:${customerId}:${dayStamp}`, entityType: 'customer', entityId: customerId, businessUnit: refs.get(customerId)?.bu,
          title: `Anomalous transaction volume: ${refs.get(customerId)?.ref ?? `customer #${customerId}`}`,
          detail: `24h volume ${gbp(recent)} is ${z.toFixed(1)}σ above the ${baselineDays}-day daily mean of ${gbp(mean)}.`,
        })
      }
      else if (z > sigma - 1) warnings++
    }
    return { breaches, warnings }
  },

  // CCM-012: same control fails its automated test twice consecutively within N days
  control_double_fail(db, now, { days = 30 }) {
    const tests = db.select().from(t.controlTests)
      .where(gte(t.controlTests.at, ago(now, days * DAY))).orderBy(desc(t.controlTests.at)).all()
    const byControl = new Map<number, typeof tests>()
    for (const x of tests) byControl.set(x.controlId, [...(byControl.get(x.controlId) ?? []), x])
    const ids = [...byControl.keys()]
    const controlRows = ids.length ? db.select().from(t.controls).where(inArray(t.controls.id, ids)).all() : []
    const breaches: Breach[] = []
    let warnings = 0
    for (const c of controlRows) {
      const [latest, previous] = byControl.get(c.id)!
      if (latest?.result === 'fail' && previous?.result === 'fail') {
        breaches.push({
          key: `control:${c.id}:${latest.id}`, entityType: 'control', entityId: c.id, businessUnit: c.businessUnit, ownerUserId: c.ownerId,
          title: `Control failed two consecutive tests: ${c.ref}`,
          detail: `"${c.name}" failed automated tests on ${previous.at.toISOString().slice(0, 10)} and ${latest.at.toISOString().slice(0, 10)}.`,
        })
      }
      else if (latest?.result === 'fail') warnings++
    }
    return { breaches, warnings }
  },
}

// §4.8 step 5 "Overdue actions escalated via CCM", a built-in monitor alongside the 12 configurable rules.
export function evaluateOverdueActions(db: Db, now: Date) {
  const rows = db.select().from(t.actions).where(and(ne(t.actions.status, 'done'), lte(t.actions.dueAt, now))).all()
  return rows.map(a => ({
    key: `action:${a.id}`, entityType: 'action', entityId: a.id, ownerUserId: a.ownerId,
    title: `Corrective action overdue: ${a.ref}`,
    detail: `"${a.title}" was due ${a.dueAt.toISOString().slice(0, 10)} (source: ${a.sourceType}).`,
  }))
}

// KRI thresholds (§4.3 step 6) are evaluated alongside CCM rules but raise 'kri' alerts.
export function evaluateKris(db: Db) {
  const rows = db.select({ kri: t.kris, risk: t.risks }).from(t.kris).innerJoin(t.risks, eq(t.risks.id, t.kris.riskId))
    .where(gte(t.kris.value, t.kris.amber)).all()
  const breaches: Breach[] = []
  let warnings = 0
  for (const { kri, risk } of rows) {
    if (kri.value >= kri.red) {
      breaches.push({
        key: `kri:${kri.id}`, entityType: 'risk', entityId: risk.id, businessUnit: risk.businessUnit, ownerUserId: risk.ownerId,
        title: `KRI limit breached: ${kri.name}`,
        detail: `${kri.value}${kri.unit} against red limit ${kri.red}${kri.unit} (risk ${risk.ref}: ${risk.title}).`,
      })
    }
    else warnings++
  }
  return { breaches, warnings }
}
