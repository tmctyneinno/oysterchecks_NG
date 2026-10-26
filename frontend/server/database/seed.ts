// Demo dataset. All dates are relative to "now" so that on first boot the CCM engine finds genuine
// breaches in the data (rather than alerts being hard-coded). Deterministic via a seeded PRNG.
import type { BetterSQLite3Database } from 'drizzle-orm/better-sqlite3'
import * as t from './schema'
import type { RoleKey } from '../../shared/utils/rbac'
import type { EscalationTarget, Severity, SlaSpec } from '../../shared/utils/sla'

type Db = BetterSQLite3Database<typeof t>

const DAY = 86_400_000
const HOUR = 3_600_000

function prng(seed: number) {
  return () => {
    seed |= 0; seed = (seed + 0x6D2B79F5) | 0
    let x = Math.imul(seed ^ (seed >>> 15), 1 | seed)
    x = (x + Math.imul(x ^ (x >>> 7), 61 | x)) ^ x
    return ((x ^ (x >>> 14)) >>> 0) / 4294967296
  }
}

export const DEMO_USERS: { email: string, name: string, role: RoleKey, businessUnit?: string }[] = [
  { email: 'cro@oysterchecks.demo', name: 'Amelia Hart', role: 'CRO' },
  { email: 'cco@oysterchecks.demo', name: 'Daniel Okafor', role: 'CCO' },
  { email: 'mlro@oysterchecks.demo', name: 'Sarah Whitfield', role: 'MLRO' },
  { email: 'analyst@oysterchecks.demo', name: 'Rahul Mehta', role: 'AML_ANALYST' },
  { email: 'compliance@oysterchecks.demo', name: 'Grace Lin', role: 'COMPLIANCE_OFFICER' },
  { email: 'audit@oysterchecks.demo', name: 'Tom Ellison', role: 'AUDIT' },
  { email: 'ciso@oysterchecks.demo', name: 'Priya Nair', role: 'CISO' },
  { email: 'hr@oysterchecks.demo', name: 'Marcus Bell', role: 'HR' },
  { email: 'retail.manager@oysterchecks.demo', name: 'Hannah Price', role: 'BU_MANAGER', businessUnit: 'Retail Banking' },
  { email: 'payments.manager@oysterchecks.demo', name: 'Oliver Grant', role: 'BU_MANAGER', businessUnit: 'Payments' },
  { email: 'board@oysterchecks.demo', name: 'Edward Collins', role: 'BOARD' },
  { email: 'admin@oysterchecks.demo', name: 'Platform Administrator', role: 'ADMIN' },
]

export const DEFAULT_RULES: {
  id: string, name: string, description: string, evaluator: string, params: Record<string, number>, severity: Severity,
  sla: SlaSpec, escalationPath: EscalationTarget[], module: string, dataSource: string
}[] = [
  { id: 'CCM-001', name: 'Unverified onboarding', description: 'Customer record created; no verified KYC after 24 hours', evaluator: 'unverified_onboarding', params: { hours: 24 }, severity: 'critical', sla: { type: 'minutes', minutes: 120 }, escalationPath: ['COMPLIANCE', 'MLRO', 'CCO', 'CRO'], module: 'AML / KYC', dataSource: 'CRM (Salesforce)' },
  { id: 'CCM-002', name: 'Unscreened high-value transaction', description: 'Transaction above threshold; no sanctions screening result', evaluator: 'unscreened_high_value', params: { threshold: 10000, graceMinutes: 5 }, severity: 'critical', sla: { type: 'minutes', minutes: 30 }, escalationPath: ['MLRO', 'CCO'], module: 'Transaction Monitoring', dataSource: 'Core Banking (Kafka)' },
  { id: 'CCM-003', name: 'SAR overdue', description: 'SAR decision recorded >7 days ago; no NCA submission', evaluator: 'sar_overdue', params: { days: 7, warnDays: 5 }, severity: 'high', sla: { type: 'end_of_day' }, escalationPath: ['MLRO', 'CCO', 'LEGAL'], module: 'AML / SAR', dataSource: 'AML case management' },
  { id: 'CCM-004', name: 'EDD incomplete for high-risk customer', description: 'High-risk flag present; EDD not completed within 10 days', evaluator: 'edd_incomplete', params: { days: 10 }, severity: 'high', sla: { type: 'minutes', minutes: 240 }, escalationPath: ['COMPLIANCE', 'MLRO'], module: 'AML / KYC', dataSource: 'CRM (Salesforce)' },
  { id: 'CCM-005', name: 'KYC refresh overdue', description: 'KYC record exceeds age limit per risk-based schedule (36/12/6 months)', evaluator: 'kyc_expired', params: { standardMonths: 36, enhancedMonths: 12, highMonths: 6, warnDays: 30 }, severity: 'medium', sla: { type: 'business_days', days: 5 }, escalationPath: ['COMPLIANCE'], module: 'AML / KYC', dataSource: 'CRM (Salesforce)' },
  { id: 'CCM-006', name: 'New starter without vetting', description: 'Employee start date within 3 days; no cleared vetting', evaluator: 'new_starter_vetting', params: { days: 3, warnDays: 10 }, severity: 'high', sla: { type: 'minutes', minutes: 1440 }, escalationPath: ['HR', 'CISO', 'CRO'], module: 'Workforce Vetting', dataSource: 'HR (Workday)' },
  { id: 'CCM-007', name: 'Privileged access not reviewed', description: 'Privileged access not reviewed for >90 days', evaluator: 'privileged_access_review', params: { days: 90, warnDays: 14 }, severity: 'medium', sla: { type: 'business_days', days: 5 }, escalationPath: ['CISO', 'CTO'], module: 'IAM / Security', dataSource: 'Active Directory (Graph API)' },
  { id: 'CCM-008', name: 'Regulatory return due', description: 'Regulatory report due in T-3 days; submission not confirmed', evaluator: 'reg_report_deadline', params: { days: 3, warnDays: 7 }, severity: 'high', sla: { type: 'before_entity_due', minutes: 1440 }, escalationPath: ['COMPLIANCE', 'CCO'], module: 'Regulatory Reporting', dataSource: 'Regulatory calendar' },
  { id: 'CCM-009', name: 'Self-assessment overdue', description: 'Control self-assessment not completed by due date', evaluator: 'csa_overdue', params: { warnDays: 3 }, severity: 'medium', sla: { type: 'business_days', days: 3 }, escalationPath: ['BU_MANAGER', 'CCO'], module: 'Combined Assurance', dataSource: 'CSA portal' },
  { id: 'CCM-010', name: 'Audit recommendation overdue', description: 'Recommendation past due date; no closure evidence', evaluator: 'audit_rec_overdue', params: { warnDays: 7 }, severity: 'high', sla: { type: 'immediate' }, escalationPath: ['HEAD_OF_AUDIT', 'CRO', 'BOARD_AUDIT_COMMITTEE'], module: 'Internal Audit', dataSource: 'Audit management system' },
  { id: 'CCM-011', name: 'Unusual transaction volume', description: 'Customer 24h volume >3 standard deviations above 90-day average', evaluator: 'anomalous_volume', params: { sigma: 3, baselineDays: 90, minBaselineDays: 10 }, severity: 'high', sla: { type: 'minutes', minutes: 120 }, escalationPath: ['MLRO'], module: 'Transaction Monitoring', dataSource: 'Core Banking (Kafka)' },
  { id: 'CCM-012', name: 'Control failed twice in a row', description: 'Same control fails automated test twice within 30 days', evaluator: 'control_double_fail', params: { days: 30 }, severity: 'critical', sla: { type: 'minutes', minutes: 240 }, escalationPath: ['CONTROL_OWNER', 'CCO', 'CRO', 'STEERING_COMMITTEE'], module: 'Controls Library', dataSource: 'Automated control testing' },
]

export function seedDatabase(db: Db, passwordHash: string, nowDate = new Date()) {
  const now = nowDate.getTime()
  const rnd = prng(20260516)
  const pick = <T>(arr: readonly T[]) => arr[Math.floor(rnd() * arr.length)]!
  const d = (offsetDays: number) => new Date(now + offsetDays * DAY)
  const h = (offsetHours: number) => new Date(now + offsetHours * HOUR)

  db.transaction((tx) => {
    // ── Users ──
    const u: Record<string, number> = {}
    for (const user of DEMO_USERS) {
      const row = tx.insert(t.users).values({ ...user, businessUnit: user.businessUnit ?? null, passwordHash }).returning({ id: t.users.id }).get()
      u[user.role === 'BU_MANAGER' ? user.businessUnit! : user.role] = row.id
    }
    const owner = { risk: u.CRO!, comp: u.CCO!, mlro: u.MLRO!, analyst: u.AML_ANALYST!, co: u.COMPLIANCE_OFFICER!, audit: u.AUDIT!, ciso: u.CISO!, hr: u.HR!, retail: u['Retail Banking']!, payments: u.Payments! }

    // ── Integrations (§2.4) ──
    tx.insert(t.integrations).values([
      { id: 'workday', name: 'HR System (Workday)', method: 'REST API (RaaS)', direction: 'HR → OC', frequency: 'Real-time + daily sync', dataElements: 'Employee ID, role, department, start date, vetting status, termination', status: 'simulated', lastSyncAt: h(-1), recordsIngested: 1240, dataQuality: 99.6 },
      { id: 'core-banking', name: 'Core Banking', method: 'REST API / Kafka stream', direction: 'Banking → OC', frequency: 'Real-time stream', dataElements: 'Transaction ID, amount, currency, sender, receiver, timestamp', status: 'simulated', lastSyncAt: new Date(now), recordsIngested: 0, dataQuality: 99.9 },
      { id: 'salesforce', name: 'CRM (Salesforce)', method: 'REST API (bidirectional)', direction: 'Bi-directional', frequency: 'Real-time events + daily', dataElements: 'Customer ID, KYC status, risk rating, relationship manager', status: 'simulated', lastSyncAt: h(-0.2), recordsIngested: 8930, dataQuality: 99.2 },
      { id: 'sap', name: 'ERP (SAP)', method: 'SAP OData API', direction: 'SAP → OC', frequency: 'Daily batch (overnight)', dataElements: 'Supplier records, payment data, financial controls output', status: 'simulated', lastSyncAt: h(-9), recordsIngested: 3120, dataQuality: 98.8 },
      { id: 'active-directory', name: 'IAM / Active Directory', method: 'Microsoft Graph API', direction: 'AD → OC', frequency: 'Event-triggered + weekly', dataElements: 'User ID, roles, permission groups, last review date', status: 'simulated', lastSyncAt: h(-30), recordsIngested: 2210, dataQuality: 99.4 },
      { id: 'lseg', name: 'Sanctions DB (Refinitiv/LSEG)', method: 'OC native connector', direction: 'External → OC', frequency: 'Real-time continuous', dataElements: 'Sanctions lists, PEP lists, adverse media feed', status: 'simulated', lastSyncAt: new Date(now), recordsIngested: 0, dataQuality: 100 },
      { id: 'audit-system', name: 'Audit Management System', method: 'REST API / CSV export', direction: 'Audit → OC', frequency: 'Daily batch', dataElements: 'Recommendations, due dates, closure status, risk rating', status: 'simulated', lastSyncAt: h(-11), recordsIngested: 186, dataQuality: 97.9 },
      { id: 'sharepoint', name: 'Document Management (SharePoint)', method: 'Microsoft Graph API', direction: 'SharePoint → OC (read)', frequency: 'On-demand', dataElements: 'Policy documents, procedure links, evidence files', status: 'disconnected', lastSyncAt: d(-6), recordsIngested: 412, dataQuality: null },
    ]).run()

    // ── CCM rules (§5) ──
    tx.insert(t.ccmRules).values(DEFAULT_RULES).run()

    // ── Controls Library ──
    const controlDefs = [
      ['C-001', 'Customer identity verification before account activation', 'Retail Banking', 'preventive', 'continuous', true, 'CCM-001', owner.retail],
      ['C-002', 'Real-time sanctions screening of outbound payments', 'Payments', 'preventive', 'continuous', true, 'CCM-002', owner.payments],
      ['C-003', 'SAR submission within regulatory timeframe', 'Retail Banking', 'corrective', 'continuous', true, 'CCM-003', owner.mlro],
      ['C-004', 'Enhanced due diligence for high-risk customers', 'Corporate Banking', 'preventive', 'continuous', true, 'CCM-004', owner.co],
      ['C-005', 'Periodic KYC refresh per risk schedule', 'Retail Banking', 'detective', 'daily', true, 'CCM-005', owner.co],
      ['C-006', 'Pre-employment vetting before system access', 'People', 'preventive', 'continuous', true, 'CCM-006', owner.hr],
      ['C-007', 'Quarterly privileged access review', 'Technology', 'detective', 'quarterly', true, 'CCM-007', owner.ciso],
      ['C-008', 'Regulatory return submission by deadline', 'Operations', 'preventive', 'continuous', true, 'CCM-008', owner.comp],
      ['C-009', 'Payment dual-authorisation above £50k', 'Payments', 'preventive', 'continuous', true, 'CCM-012', owner.payments],
      ['C-010', 'Daily nostro reconciliation', 'Operations', 'detective', 'daily', true, 'CCM-012', owner.payments],
      ['C-011', 'Transaction monitoring scenario tuning review', 'Payments', 'detective', 'quarterly', false, null, owner.mlro],
      ['C-012', 'Change management approval for production releases', 'Technology', 'preventive', 'continuous', true, 'CCM-012', owner.ciso],
      ['C-013', 'Branch cash limit monitoring', 'Retail Banking', 'detective', 'daily', true, null, owner.retail],
      ['C-014', 'Complaints root-cause review', 'Retail Banking', 'corrective', 'monthly', false, null, owner.retail],
      ['C-015', 'Supplier due diligence before contract award', 'Operations', 'preventive', 'continuous', false, null, owner.comp],
      ['C-016', 'Data loss prevention on outbound email', 'Technology', 'preventive', 'continuous', true, null, owner.ciso],
    ] as const
    const effectiveness = ['effective', 'effective', 'partially_effective', 'effective', 'partially_effective', 'effective', 'ineffective', 'effective', 'ineffective', 'effective', 'partially_effective', 'effective', 'effective', 'not_tested', 'partially_effective', 'effective'] as const
    const controlIds: number[] = []
    controlDefs.forEach(([ref, name, bu, type, frequency, automated, rule, ownerId], i) => {
      const row = tx.insert(t.controls).values({ ref, name, businessUnit: bu, type, frequency, automated, ccmRuleId: rule, ownerId, effectiveness: effectiveness[i]!, lastTestedAt: d(-1 - i % 5), description: `${name}. Owned by ${bu}.` }).returning({ id: t.controls.id }).get()
      controlIds.push(row.id)
    })
    // Test history consistent with each control's effectiveness (effectiveness is derived from tests
    // at runtime): ineffective = latest two failed (→ CCM-012), partially effective = one recent failure.
    for (const [i, id] of controlIds.entries()) {
      const eff = effectiveness[i]!
      if (eff === 'not_tested') continue
      for (let k = 6; k >= 1; k--) {
        const failing = (eff === 'ineffective' && k <= 2) || (eff === 'partially_effective' && k === 2)
        tx.insert(t.controlTests).values({ controlId: id, at: d(-k * 4 - 0.3), result: failing ? 'fail' : 'pass', method: controlDefs[i]![5] ? 'automated' : 'manual', testerId: controlDefs[i]![5] ? null : owner.co, notes: failing ? 'Test exception: sample failed control criteria' : null }).run()
      }
    }

    // ── Risks + KRIs (§4.3) ──
    const riskDefs = [
      ['R-001', 'Unscreened cross-border payments', 'Financial Crime', 5, 4, 'mitigate', owner.mlro, 'Payments', [1, 10]],
      ['R-002', 'Sanctions list latency', 'Financial Crime', 4, 3, 'mitigate', owner.comp, 'Payments', [1]],
      ['R-003', 'Third-party data breach', 'Cyber & Data', 5, 3, 'mitigate', owner.ciso, 'Technology', [15]],
      ['R-004', 'Privileged access sprawl', 'Cyber & Data', 3, 4, 'mitigate', owner.ciso, 'Technology', [6]],
      ['R-005', 'Late regulatory reporting', 'Regulatory', 4, 2, 'mitigate', owner.comp, 'Operations', [7]],
      ['R-006', 'Incomplete or late SAR filings', 'Financial Crime', 5, 2, 'mitigate', owner.mlro, 'Retail Banking', [2]],
      ['R-007', 'Critical supplier concentration', 'Operational', 3, 3, 'accept', owner.risk, 'Operations', [14]],
      ['R-008', 'Vetting backlog for new starters', 'People', 2, 4, 'mitigate', owner.hr, 'People', [5]],
      ['R-009', 'AI risk-scoring model drift', 'Model', 3, 2, 'mitigate', owner.risk, 'Technology', []],
      ['R-010', 'Payment authorisation bypass', 'Operational', 5, 3, 'mitigate', owner.payments, 'Payments', [8, 9]],
      ['R-011', 'ESG disclosure inaccuracy', 'Regulatory', 3, 2, 'accept', owner.comp, 'Operations', []],
      ['R-012', 'Legacy integration failure', 'Operational', 4, 2, 'transfer', owner.ciso, 'Technology', [11]],
      ['R-013', 'Mis-selling in retail products', 'Conduct', 4, 2, 'mitigate', owner.retail, 'Retail Banking', [13]],
    ] as const
    const riskIds: number[] = []
    for (const [ref, title, category, impact, likelihood, treatment, ownerId, bu, ctrl] of riskDefs) {
      const r = tx.insert(t.risks).values({ ref, title, category, impact, likelihood, treatment, ownerId, businessUnit: bu, lastReviewedAt: d(-20), treatmentPlan: treatment === 'mitigate' ? 'Strengthen mapped controls and extend CCM coverage.' : null, treatmentDueAt: d(45) }).returning({ id: t.risks.id }).get()
      riskIds.push(r.id)
      for (const ci of ctrl) tx.insert(t.links).values({ fromType: 'risk', fromId: r.id, toType: 'control', toId: controlIds[ci]! }).run()
    }
    const series = (base: number, drift: number) => Array.from({ length: 12 }, (_, i) => Math.round((base + drift * i + (rnd() - 0.5) * base * 0.15) * 10) / 10)
    tx.insert(t.kris).values([
      { riskId: riskIds[0]!, name: 'Unscreened payments >£10k (24h)', unit: '', value: 3, amber: 2, red: 5, history: series(1, 0.15), updatedAt: new Date(now) },
      { riskId: riskIds[3]!, name: 'Privileged accounts overdue review', unit: '%', value: 0, amber: 10, red: 20, history: series(8, 1.5), updatedAt: new Date(now) },
      { riskId: riskIds[7]!, name: 'Average vetting clearance time', unit: ' days', value: 8.5, amber: 9, red: 12, history: series(7, 0.12), updatedAt: new Date(now) },
      { riskId: riskIds[9]!, name: 'Dual-auth exceptions (7d)', unit: '', value: 7, amber: 2, red: 6, history: series(1, 0.25), updatedAt: new Date(now) },
      { riskId: riskIds[2]!, name: 'Critical suppliers without cyber review', unit: '', value: 1, amber: 2, red: 3, history: series(2, -0.05), updatedAt: new Date(now) },
    ]).run()

    // ── Compliance obligations & regulatory reports ──
    const obligationDefs = [
      ['OB-001', 'Money Laundering Regulations 2017: CDD & EDD', 'regulation', 'MLR 2017', 'HM Treasury / FCA', 'compliant'],
      ['OB-002', 'SYSC 6.3: Financial crime systems and controls', 'regulation', 'FCA SYSC', 'FCA', 'partial'],
      ['OB-003', 'Proceeds of Crime Act: SAR reporting obligations', 'law', 'POCA 2002', 'NCA', 'partial'],
      ['OB-004', 'UK GDPR Article 32: Security of processing', 'law', 'UK GDPR', 'ICO', 'compliant'],
      ['OB-005', 'UK GDPR Article 35: DPIA for high-risk processing', 'law', 'UK GDPR', 'ICO', 'compliant'],
      ['OB-006', 'PRA SS1/21: Operational resilience', 'regulation', 'PRA Operational Resilience', 'PRA', 'partial'],
      ['OB-007', 'Consumer Duty: outcomes monitoring', 'regulation', 'FCA Consumer Duty', 'FCA', 'non_compliant'],
      ['OB-008', 'SM&CR: Certification regime', 'regulation', 'FCA SM&CR', 'FCA', 'compliant'],
      ['OB-009', 'ISO 27001 A.5.15: Access control', 'standard', 'ISO 27001', null, 'partial'],
      ['OB-010', 'Sanctions and Anti-Money Laundering Act 2018', 'law', 'SAMLA 2018', 'OFSI', 'compliant'],
      ['OB-011', 'Modern Slavery Act s.54: Supply chain statement', 'law', 'Modern Slavery Act', 'Home Office', 'compliant'],
      ['OB-012', 'Information security policy adherence', 'internal_policy', 'Internal', null, 'not_assessed'],
      ['OB-013', 'UK Bribery Act s.7: Adequate procedures', 'law', 'UKBA 2010', 'SFO', 'compliant'],
      ['OB-014', 'BCBS 239: Risk data aggregation', 'standard', 'BCBS 239', 'PRA', 'partial'],
    ] as const
    // Which controls evidence each obligation (indexes into controlDefs).
    const obligationControls: Record<string, number[]> = {
      'OB-001': [0, 3, 4], 'OB-002': [1, 10], 'OB-003': [2], 'OB-004': [6, 15], 'OB-005': [15], 'OB-006': [9, 11],
      'OB-007': [13], 'OB-008': [5], 'OB-009': [6, 11], 'OB-010': [1], 'OB-011': [14], 'OB-013': [14], 'OB-014': [7],
    }
    for (const [ref, title, sourceType, framework, regulator, status] of obligationDefs) {
      const o = tx.insert(t.obligations).values({ ref, title, sourceType, framework, regulator, jurisdiction: 'UK', ownerId: pick([owner.comp, owner.co, owner.mlro]), status, lastAssessedAt: d(-30 - Math.floor(rnd() * 60)), nextReviewAt: d(30 + Math.floor(rnd() * 120)) }).returning({ id: t.obligations.id }).get()
      for (const ci of obligationControls[ref] ?? []) tx.insert(t.links).values({ fromType: 'obligation', fromId: o.id, toType: 'control', toId: controlIds[ci]! }).run()
    }
    tx.insert(t.regulatoryReports).values([
      { name: 'FSA001 Balance Sheet', regulator: 'FCA', frequency: 'Quarterly', dueAt: d(12), automated: true, preparedAt: d(-1), ownerId: owner.comp },
      { name: 'REP-CRIM Financial Crime Return', regulator: 'FCA', frequency: 'Annual', dueAt: d(20), automated: true, preparedAt: null, ownerId: owner.mlro },
      { name: 'COREP Own Funds', regulator: 'PRA', frequency: 'Quarterly', dueAt: d(21), automated: true, ownerId: owner.comp },
      { name: 'Operational Resilience Self-Assessment', regulator: 'PRA', frequency: 'Annual', dueAt: d(48), automated: false, ownerId: owner.risk },
      { name: 'PSD2 Fraud Statistics', regulator: 'FCA', frequency: 'Half-yearly', dueAt: d(-12), automated: true, preparedAt: d(-16), submittedAt: d(-13), submittedById: owner.comp, reference: 'FCA-PSD-88231', ownerId: owner.comp },
      { name: 'ICO Annual Data Protection Fee', regulator: 'ICO', frequency: 'Annual', dueAt: d(-40), automated: false, submittedAt: d(-44), submittedById: owner.comp, reference: 'ZA-551203', ownerId: owner.comp },
      { name: 'Complaints Return (DISP 1.10)', regulator: 'FCA', frequency: 'Half-yearly', dueAt: d(-5), automated: true, preparedAt: d(-9), submittedAt: d(-6), submittedById: owner.co, reference: 'FCA-DISP-23310', ownerId: owner.co },
    ]).run()

    // ── Governance ──
    const policyDefs = [
      ['POL-001', 'Anti-Money Laundering & CTF Policy', owner.mlro, 'approved', ['MLR 2017', 'POCA 2002'], 120, 4],
      ['POL-002', 'Sanctions Compliance Policy', owner.mlro, 'approved', ['SAMLA 2018'], 45, 3],
      ['POL-003', 'Information Security Policy', owner.ciso, 'approved', ['ISO 27001', 'UK GDPR'], 15, 6],
      ['POL-004', 'Data Protection & Retention Policy', owner.comp, 'in_review', ['UK GDPR'], -10, 3],
      ['POL-005', 'Third-Party Risk Management Policy', owner.comp, 'approved', ['PRA SS2/21'], 200, 2],
      ['POL-006', 'Whistleblowing Policy', owner.risk, 'approved', ['PIDA 1998'], 90, 2],
      ['POL-007', 'Operational Resilience Framework', owner.risk, 'draft', ['PRA SS1/21'], 60, 1],
      ['POL-008', 'Pre-Employment Screening Policy', owner.hr, 'approved', ['BS7858', 'BPSS'], 5, 2],
    ] as const
    for (const [ref, title, ownerId, status, frameworks, reviewIn, version] of policyDefs) {
      const p = tx.insert(t.policies).values({ ref, title, ownerId, status, frameworks: [...frameworks], version, jurisdiction: 'UK', reviewDate: d(reviewIn), approvedById: status === 'approved' ? owner.risk : null, approvedAt: status === 'approved' ? d(-200) : null, summary: `${title}, sets minimum standards, roles and responsibilities.` }).returning({ id: t.policies.id }).get()
      tx.insert(t.policyVersions).values({ policyId: p.id, version, status, note: 'Imported at platform go-live', actorId: ownerId, at: d(-200) }).run()
      if (status === 'approved') {
        for (const [i, userId] of Object.values(u).entries()) {
          if (rnd() < 0.75) tx.insert(t.policyAcknowledgements).values({ policyId: p.id, userId, version, at: d(-100 + i) }).onConflictDoNothing().run()
        }
      }
    }

    // ── Combined assurance (§4.6) ──
    const activities: [1 | 2 | 3, typeof t.assuranceActivities.$inferInsert['provider'], string, typeof t.assuranceActivities.$inferInsert['activityType'], number, number, 'planned' | 'in_progress' | 'complete', 'satisfactory' | 'needs_improvement' | 'unsatisfactory' | null, number[]][] = [
      [1, 'management', 'Monthly self-assessment: Retail Banking key controls', 'csa', -20, 5, 'in_progress', null, [0, 4, 12, 13]],
      [1, 'management', 'Monthly self-assessment: Payments key controls', 'csa', -20, 5, 'in_progress', null, [1, 8, 9]],
      [1, 'management', 'Quarterly access recertification', 'review', -60, -30, 'complete', 'needs_improvement', [6]],
      [2, 'compliance', 'AML monitoring programme quarterly review', 'monitoring', -45, 15, 'in_progress', null, [0, 1, 2, 3, 4]],
      [2, 'compliance', 'Regulatory reporting accuracy testing', 'testing', -90, -60, 'complete', 'satisfactory', [7]],
      [2, 'risk', 'Operational risk review: payments', 'review', -30, 10, 'in_progress', null, [8, 9]],
      [2, 'compliance', 'Consumer Duty outcomes testing', 'testing', 10, 40, 'planned', null, [13]],
      [3, 'internal_audit', 'Financial crime framework audit', 'audit', -75, -40, 'complete', 'needs_improvement', [0, 1, 2, 3]],
      [3, 'internal_audit', 'IT general controls audit', 'audit', -15, 20, 'in_progress', null, [6, 11, 15]],
      [3, 'internal_audit', 'Third-party risk management audit', 'audit', 30, 60, 'planned', null, [14]],
      [3, 'external_audit', 'Statutory audit: controls reliance testing', 'audit', -120, -90, 'complete', 'satisfactory', [9]],
      [3, 'regulator', 'FCA financial crime systems review', 'review', -200, -170, 'complete', 'needs_improvement', [1, 2]],
    ]
    for (const [line, provider, title, activityType, s, e, status, rating, ctrl] of activities) {
      const a = tx.insert(t.assuranceActivities).values({ line, provider, title, activityType, plannedStart: d(s), plannedEnd: d(e), status, rating, ownerId: line === 1 ? owner.retail : line === 2 ? owner.co : owner.audit }).returning({ id: t.assuranceActivities.id }).get()
      for (const ci of ctrl) tx.insert(t.links).values({ fromType: 'assurance', fromId: a.id, toType: 'control', toId: controlIds[ci]! }).run()
    }

    // ── CSA (CCM-009): current period, Retail Banking has two overdue ──
    const period = new Date(now).toISOString().slice(0, 7)
    controlDefs.forEach(([, , bu], i) => {
      if (bu !== 'Retail Banking' && bu !== 'Payments') return
      const overdue = bu === 'Retail Banking' && (i === 12 || i === 13)
      const submitted = !overdue && rnd() < 0.6
      tx.insert(t.csaAssessments).values({
        controlId: controlIds[i]!, businessUnit: bu, period,
        dueAt: overdue ? d(5) : d(6),
        submittedAt: submitted ? d(-2) : null,
        submittedById: submitted ? (bu === 'Retail Banking' ? owner.retail : owner.payments) : null,
        result: submitted ? pick(['effective', 'effective', 'partially_effective'] as const) : null,
      }).run()
    })

    // ── Internal audit (CCM-010) ──
    const engagements = [
      ['IA-2026-01', 'Financial crime framework', 'Retail Banking', 'closed', -75, -40, 'needs_improvement'],
      ['IA-2026-02', 'IT general controls', 'Technology', 'fieldwork', -15, 20, null],
      ['IA-2026-03', 'Third-party risk management', 'Operations', 'planned', 30, 60, null],
      ['IA-2026-04', 'Payments operations', 'Payments', 'reporting', -40, 2, null],
    ] as const
    const engIds: number[] = []
    for (const [ref, title, bu, status, s, e, rating] of engagements) {
      engIds.push(tx.insert(t.auditEngagements).values({ ref, title, businessUnit: bu, status, plannedStart: d(s), plannedEnd: d(e), rating, leadId: owner.audit, scope: `Design and operating effectiveness of ${title.toLowerCase()} controls.` }).returning({ id: t.auditEngagements.id }).get().id)
    }
    tx.insert(t.auditFindings).values([
      { ref: 'F-2026-001', engagementId: engIds[0]!, title: 'EDD files missing source-of-wealth evidence', rating: 'high', ownerId: owner.co, businessUnit: 'Corporate Banking', dueAt: d(-3), status: 'open', managementResponse: 'Agreed. Remediation of 14 files in progress.' },
      { ref: 'F-2026-002', engagementId: engIds[0]!, title: 'Sanctions screening fuzzy-match threshold not documented', rating: 'medium', ownerId: owner.mlro, businessUnit: 'Payments', dueAt: d(5), status: 'open', managementResponse: 'Agreed. Threshold rationale to be documented.' },
      { ref: 'F-2026-003', engagementId: engIds[0]!, title: 'SAR quality assurance checks not evidenced', rating: 'medium', ownerId: owner.mlro, businessUnit: 'Retail Banking', dueAt: d(-30), status: 'closed', closedAt: d(-32), closureEvidence: 'QA checklist implemented; sample of 20 SARs reviewed.' },
      { ref: 'F-2026-004', engagementId: engIds[3]!, title: 'Dual-authorisation override log not reviewed', rating: 'high', ownerId: owner.payments, businessUnit: 'Payments', dueAt: d(25), status: 'open' },
      { ref: 'F-2026-005', engagementId: engIds[1]!, title: 'Leaver access not revoked within 24 hours', rating: 'medium', ownerId: owner.ciso, businessUnit: 'Technology', dueAt: d(40), status: 'open' },
    ]).run()

    // ── Customers, transactions & AML cases (§4.5) ──
    const countries = [['GB', 'low'], ['GB', 'low'], ['GB', 'low'], ['IE', 'low'], ['DE', 'low'], ['AE', 'medium'], ['NG', 'medium'], ['CY', 'medium'], ['PA', 'high'], ['VG', 'high']] as const
    const custDefs: { name: string, type: 'individual' | 'entity', rating: 'standard' | 'enhanced' | 'high', created: number, kyc: 'verified' | 'pending', verifiedAgoDays?: number, edd?: 'required' | 'complete' | 'not_required', pep?: boolean, bu: string, c: number }[] = [
      { name: 'Halcyon Trading Ltd', type: 'entity', rating: 'high', created: -400, kyc: 'verified', verifiedAgoDays: 120, edd: 'complete', bu: 'Corporate Banking', c: 7 },
      { name: 'Meridian Holdings SA', type: 'entity', rating: 'standard', created: -1.3, kyc: 'pending', bu: 'Corporate Banking', c: 4 },
      { name: 'Joanna Pryce', type: 'individual', rating: 'standard', created: -1.6, kyc: 'pending', bu: 'Retail Banking', c: 0 },
      { name: 'Kestrel Logistics Ltd', type: 'entity', rating: 'standard', created: -0.12, kyc: 'pending', bu: 'Corporate Banking', c: 1 },
      { name: 'Blackwater Freight Ltd', type: 'entity', rating: 'high', created: -6, kyc: 'verified', verifiedAgoDays: 5, edd: 'complete', bu: 'Corporate Banking', c: 8 },
      { name: 'Nana Osei-Bonsu', type: 'individual', rating: 'enhanced', created: -700, kyc: 'verified', verifiedAgoDays: 250, pep: true, bu: 'Retail Banking', c: 6 },
      { name: 'Daniel Fairweather', type: 'individual', rating: 'high', created: -220, kyc: 'verified', verifiedAgoDays: 150, edd: 'complete', bu: 'Retail Banking', c: 9 },
      { name: 'Orion Capital Partners', type: 'entity', rating: 'standard', created: -1500, kyc: 'verified', verifiedAgoDays: 900, bu: 'Corporate Banking', c: 0 },
      { name: 'Aldgate Bakery Co', type: 'entity', rating: 'standard', created: -1200, kyc: 'verified', verifiedAgoDays: 950, bu: 'Retail Banking', c: 0 },
      { name: 'Seren Patel', type: 'individual', rating: 'standard', created: -900, kyc: 'verified', verifiedAgoDays: 400, bu: 'Retail Banking', c: 0 },
      { name: 'Lumen Software Ltd', type: 'entity', rating: 'standard', created: -600, kyc: 'verified', verifiedAgoDays: 590, bu: 'Corporate Banking', c: 3 },
      { name: 'Ivo Marković', type: 'individual', rating: 'enhanced', created: -500, kyc: 'verified', verifiedAgoDays: 190, bu: 'Retail Banking', c: 5 },
      { name: 'Thames Valley Lettings', type: 'entity', rating: 'standard', created: -800, kyc: 'verified', verifiedAgoDays: 780, bu: 'Retail Banking', c: 1 },
      { name: 'Gulfstream Commodities FZE', type: 'entity', rating: 'high', created: -300, kyc: 'verified', verifiedAgoDays: 100, edd: 'complete', bu: 'Corporate Banking', c: 5 },
      { name: 'Eleanor Whitcombe', type: 'individual', rating: 'standard', created: -1000, kyc: 'verified', verifiedAgoDays: 980, bu: 'Retail Banking', c: 2 },
      { name: 'Northwind Imports Ltd', type: 'entity', rating: 'enhanced', created: -420, kyc: 'verified', verifiedAgoDays: 350, bu: 'Corporate Banking', c: 3 },
      { name: 'Femi Adebayo', type: 'individual', rating: 'standard', created: -350, kyc: 'verified', verifiedAgoDays: 340, bu: 'Retail Banking', c: 6 },
      { name: 'Castellan Yachts SL', type: 'entity', rating: 'high', created: -260, kyc: 'verified', verifiedAgoDays: 160, edd: 'complete', bu: 'Corporate Banking', c: 9 },
    ]
    const customerIds: number[] = []
    custDefs.forEach((c, i) => {
      const [country, countryRisk] = countries[c.c]!
      const row = tx.insert(t.customers).values({
        ref: `CUS-${String(48200 + i)}`, name: c.name, type: c.type, country, countryRisk,
        dob: c.type === 'individual' ? `19${60 + Math.floor(rnd() * 39)}-0${1 + Math.floor(rnd() * 9)}-1${Math.floor(rnd() * 9)}` : null,
        businessType: c.type === 'entity' ? pick(['Wholesale trade', 'Logistics', 'Investment', 'Software', 'Commodities', 'Property']) : null,
        riskRating: c.rating, kycStatus: c.kyc, kycVerifiedAt: c.verifiedAgoDays ? d(-c.verifiedAgoDays) : null,
        pep: !!c.pep, sanctionsStatus: c.kyc === 'pending' ? 'pending' : 'clear', adverseMedia: c.rating === 'high',
        eddStatus: c.edd ?? (c.rating === 'high' ? 'required' : 'not_required'), eddCompletedAt: c.edd === 'complete' ? d(-c.verifiedAgoDays! + 5) : null,
        businessUnit: c.bu, createdAt: d(c.created),
      }).returning({ id: t.customers.id }).get()
      customerIds.push(row.id)
    })

    // 90 days of screened transaction history for verified customers.
    const counterparties = [['Acme Supplies Ltd', 'GB'], ['Rhine Metals GmbH', 'DE'], ['Dublin Freight', 'IE'], ['Atlas Trading LLC', 'AE'], ['Lagos Agro Ltd', 'NG'], ['Coral Bay Holdings', 'VG'], ['HMRC', 'GB'], ['Payroll', 'GB']] as const
    let txSeq = 1
    const txRows: typeof t.transactions.$inferInsert[] = []
    custDefs.forEach((c, i) => {
      if (c.kyc !== 'verified') return
      const scale = c.type === 'entity' ? 9000 : 900
      for (let day = 90; day >= 1; day--) {
        const n = Math.floor(rnd() * 3)
        for (let k = 0; k < n; k++) {
          const [cp, cc] = pick(counterparties)
          txRows.push({ ref: `TX-${String(txSeq++).padStart(7, '0')}`, customerId: customerIds[i]!, amount: Math.round(scale * (0.3 + rnd() * 1.4)), currency: 'GBP', direction: rnd() < 0.55 ? 'out' : 'in', counterparty: cp, counterpartyCountry: cc, at: new Date(now - day * DAY - rnd() * DAY * 0.9), screeningStatus: 'clear' })
        }
      }
    })
    // Today: Halcyon spike (CCM-011) including an unscreened £340k wire (CCM-002).
    txRows.push({ ref: `TX-${String(txSeq++).padStart(7, '0')}`, customerId: customerIds[0]!, amount: 340_000, currency: 'GBP', direction: 'out', counterparty: 'Coral Bay Holdings', counterpartyCountry: 'VG', at: h(-0.4), screeningStatus: 'pending' })
    txRows.push({ ref: `TX-${String(txSeq++).padStart(7, '0')}`, customerId: customerIds[0]!, amount: 96_500, currency: 'GBP', direction: 'out', counterparty: 'Atlas Trading LLC', counterpartyCountry: 'AE', at: h(-3), screeningStatus: 'clear' })
    txRows.push({ ref: `TX-${String(txSeq++).padStart(7, '0')}`, customerId: customerIds[0]!, amount: 120_000, currency: 'GBP', direction: 'out', counterparty: 'Coral Bay Holdings', counterpartyCountry: 'VG', at: h(-6), screeningStatus: 'clear' })
    for (let i = 0; i < txRows.length; i += 500) tx.insert(t.transactions).values(txRows.slice(i, i + 500)).run()

    const caseDefs: [number, typeof t.amlCases.$inferInsert['type'], number, typeof t.amlCases.$inferInsert['status'], number, number | null, { factor: string, weight: number }[]][] = [
      [0, 'transaction', 91, 'under_review', -0.3, owner.analyst, [{ factor: 'High-risk jurisdiction counterparty (VG)', weight: 30 }, { factor: 'Volume >3σ above baseline', weight: 35 }, { factor: 'High customer risk rating', weight: 26 }]],
      [5, 'sanctions', 78, 'under_review', -1, owner.mlro, [{ factor: 'PEP status', weight: 40 }, { factor: 'Fuzzy name match (LSEG)', weight: 38 }]],
      [1, 'kyc', 64, 'new', -0.5, null, [{ factor: 'KYB not verified after 24h', weight: 44 }, { factor: 'Medium-risk jurisdiction', weight: 20 }]],
      [6, 'edd', 55, 'escalated', -4, owner.analyst, [{ factor: 'Source of wealth unverified', weight: 35 }, { factor: 'High-risk jurisdiction', weight: 20 }]],
      [16, 'transaction', 88, 'sar_decided', -12, owner.mlro, [{ factor: 'Structuring: sub-threshold transfers', weight: 48 }, { factor: 'Medium-risk jurisdiction (NG)', weight: 40 }]],
      [17, 'transaction', 72, 'sar_decided', -7, owner.mlro, [{ factor: 'Rapid movement of funds', weight: 42 }, { factor: 'High-risk jurisdiction', weight: 30 }]],
      [11, 'transaction', 69, 'sar_submitted', -30, owner.mlro, [{ factor: 'Cash-intensive pattern', weight: 69 }]],
      [9, 'sanctions', 22, 'closed', -20, owner.analyst, [{ factor: 'Name match, secondary identifiers differ', weight: 22 }]],
    ]
    caseDefs.forEach(([ci, type, score, status, opened, analystId, factors], i) => {
      const sarDecisionAt = status === 'sar_decided' ? d(opened + (i === 4 ? 3 : 1)) : status === 'sar_submitted' ? d(opened + 3) : null
      tx.insert(t.amlCases).values({
        ref: `AML-${2190 + i}`, customerId: customerIds[ci]!, type, score, scoreFactors: factors, status, analystId, openedAt: d(opened),
        sarDecisionAt, sarSubmittedAt: status === 'sar_submitted' ? d(opened + 6) : null, ncaReference: status === 'sar_submitted' ? 'NCA-SAR-7741902' : null,
        sarDraft: sarDecisionAt ? `Subject: ${custDefs[ci]!.name}. Grounds for suspicion: ${factors.map(f => f.factor).join('; ')}.` : null,
        outcome: status === 'closed' ? 'False positive. No further action.' : null, closedAt: status === 'closed' ? d(opened + 2) : null,
      }).run()
    })

    // ── Workforce vetting & privileged access ──
    const employeeDefs = [
      ['Chloe Bennett', 'Payments Operations', 'Payments Analyst', 'Payments', 14, 'BPSS', 'in_progress'],
      ['Jamal Hussein', 'Technology', 'Platform Engineer', 'Technology', 12, 'BS7858', 'in_progress'],
      ['Laura Kim', 'Retail Banking', 'Customer Adviser', 'Retail Banking', 20, 'DBS', 'not_started'],
      ['Peter Novak', 'Financial Crime', 'AML Analyst', 'Operations', -30, 'BPSS', 'cleared'],
      ['Aisha Rahman', 'Technology', 'Security Analyst', 'Technology', -90, 'BS7858', 'cleared'],
      ['George Mills', 'Retail Banking', 'Branch Manager', 'Retail Banking', -400, 'DBS', 'cleared'],
      ['Isabel Cruz', 'Corporate Banking', 'Relationship Manager', 'Corporate Banking', -200, 'BPSS', 'cleared'],
      ['Samuel Owusu', 'Operations', 'Reconciliations Lead', 'Operations', -150, 'BPSS', 'cleared'],
    ] as const
    employeeDefs.forEach(([name, department, jobTitle, bu, start, vettingType, vettingStatus], i) => {
      tx.insert(t.employees).values({ ref: `EMP-${String(1040 + i)}`, name, department, jobTitle, businessUnit: bu, startDate: d(start), vettingType, vettingStatus, vettingClearedAt: vettingStatus === 'cleared' ? d(start - 5) : null, dob: `19${70 + i * 3}-0${1 + i}-1${i}` }).run()
    })
    tx.insert(t.accessAccounts).values([
      { account: 'adm-jhussein', system: 'Active Directory', holder: 'Jamal Hussein', privileged: true, groups: ['Domain Admins'], lastReviewedAt: d(-60), reviewedById: owner.ciso },
      { account: 'svc-payments-batch', system: 'Core Banking', holder: 'Service account', privileged: true, groups: ['Payments Release'], lastReviewedAt: d(-70), reviewedById: owner.ciso },
      { account: 'adm-anair', system: 'Azure', holder: 'Aisha Rahman', privileged: true, groups: ['Global Administrator'], lastReviewedAt: d(-45), reviewedById: owner.ciso },
      { account: 'dba-smills', system: 'Core Banking DB', holder: 'Samuel Owusu', privileged: true, groups: ['db_owner'], lastReviewedAt: d(-30), reviewedById: owner.ciso },
      { account: 'adm-network', system: 'Firewall', holder: 'Network team', privileged: true, groups: ['Superuser'], lastReviewedAt: d(-40), reviewedById: owner.ciso },
      { account: 'sap-firefighter', system: 'SAP', holder: 'Finance support', privileged: true, groups: ['SAP_ALL'], lastReviewedAt: d(-10), reviewedById: owner.ciso },
      { account: 'gbennett', system: 'Active Directory', holder: 'George Mills', privileged: false, groups: ['Branch Staff'], lastReviewedAt: d(-200) },
      { account: 'icruz', system: 'Salesforce', holder: 'Isabel Cruz', privileged: false, groups: ['Relationship Managers'], lastReviewedAt: d(-60) },
    ]).run()

    // ── Third parties (§4.7) ──
    const supplierDefs = [
      ['Northgate Cloud Services', 'Core banking hosting', 'GB', 'critical', true, 2_400_000, 'complete', 82, 'clear', [], -200, 165, 400, true],
      ['Sentinel Card Processing', 'Card issuing & processing', 'IE', 'critical', true, 1_100_000, 'complete', 74, 'clear', ['Cyber incident reported (sector)'], -380, -15, 120, true],
      ['Verity ID Ltd', 'Biometric identity verification', 'GB', 'high', true, 380_000, 'complete', 88, 'clear', [], -100, 450, 300, true],
      ['Pinnacle Facilities', 'Facilities management', 'GB', 'medium', false, 210_000, 'complete', 69, 'clear', [], -300, 430, 500, false],
      ['Atlas Offshore Support', 'Offshore operations support', 'IN', 'high', true, 640_000, 'in_progress', null, 'pending', ['Financial health: rating downgrade'], -500, -30, 60, false],
      ['Hexa Data Analytics', 'Data analytics platform', 'US', 'high', true, 290_000, 'complete', 77, 'clear', [], -150, 400, 350, true],
      ['Bright Print & Post', 'Statement printing', 'GB', 'low', true, 90_000, 'complete', 71, 'clear', [], -600, 130, 200, true],
      ['Kraken Couriers', 'Secure courier services', 'GB', 'medium', false, 45_000, 'complete', 63, 'potential_match', ['Adverse media: labour practices'], -250, 480, 90, false],
      ['Quantum Legal LLP', 'External legal advice', 'GB', 'medium', false, 150_000, 'pending', null, 'pending', [], 0, 30, 365, false],
    ] as const
    supplierDefs.forEach(([name, services, country, tier, dataAccess, spend, dd, score, sanctions, flags, last, next, renewal, dpa], i) => {
      tx.insert(t.suppliers).values({ ref: `SUP-${String(301 + i)}`, name, services, country, tier, dataAccess, annualSpend: spend, ddStatus: dd, questionnaireScore: score, sanctionsStatus: sanctions, monitoringFlags: [...flags], status: dd === 'pending' ? 'onboarding' : 'active', lastReviewAt: last ? d(last) : null, nextReviewAt: d(next), contractRenewalAt: d(renewal), dpaInPlace: dpa, ownerId: owner.comp }).run()
    })

    // ── Incidents & corrective actions (§4.8) ──
    const incidentRows = [
      { ref: 'INC-0101', title: 'Payment released without second authoriser', type: 'operational', severity: 'high', status: 'investigating', businessUnit: 'Payments', ownerId: owner.payments, occurredAt: d(-6), rcaMethod: '5_whys', rootCause: 'Temporary override role left active after system upgrade.' },
      { ref: 'INC-0102', title: 'Customer data emailed to wrong recipient', type: 'data', severity: 'medium', status: 'remediating', businessUnit: 'Retail Banking', ownerId: owner.retail, occurredAt: d(-3), notifiable: true, regulator: 'ICO', notificationDueAt: h(-3 * 24 + 72), rootCause: 'Autocomplete selected an external address.' },
      { ref: 'INC-0103', title: 'Sanctions screening service latency', type: 'financial_crime', severity: 'critical', status: 'open', businessUnit: 'Payments', ownerId: owner.mlro, occurredAt: h(-5) },
      { ref: 'INC-0104', title: 'Phishing campaign targeting finance team', type: 'cyber', severity: 'medium', status: 'closed', businessUnit: 'Technology', ownerId: owner.ciso, occurredAt: d(-25), rcaMethod: 'ishikawa', rootCause: 'Gaps in mail filtering rules.', lessonsLearned: 'Mail filtering updated; targeted training delivered.', closedAt: d(-12), closedById: owner.ciso },
      { ref: 'INC-0105', title: 'Late complaint acknowledgement letters', type: 'regulatory', severity: 'low', status: 'closed', businessUnit: 'Retail Banking', ownerId: owner.retail, occurredAt: d(-40), rootCause: 'Print vendor backlog.', lessonsLearned: 'SLA monitoring added to vendor scorecard.', closedAt: d(-28), closedById: owner.co },
    ] as const
    const incIds: number[] = []
    for (const r of incidentRows) incIds.push(tx.insert(t.incidents).values({ ...r, reportedById: r.ownerId, reportedAt: new Date(r.occurredAt.getTime() + HOUR * 2) }).returning({ id: t.incidents.id }).get().id)
    tx.insert(t.links).values([
      { fromType: 'incident', fromId: incIds[0]!, toType: 'control', toId: controlIds[8]! },
      { fromType: 'incident', fromId: incIds[0]!, toType: 'risk', toId: riskIds[9]! },
      { fromType: 'incident', fromId: incIds[2]!, toType: 'risk', toId: riskIds[1]! },
      { fromType: 'incident', fromId: incIds[2]!, toType: 'control', toId: controlIds[1]! },
      { fromType: 'incident', fromId: incIds[1]!, toType: 'control', toId: controlIds[15]! },
    ]).run()
    tx.insert(t.actions).values([
      { ref: 'ACT-0001', title: 'Remove residual override roles post-upgrade', sourceType: 'incident', sourceId: incIds[0]!, ownerId: owner.ciso, dueAt: d(4), status: 'in_progress' },
      { ref: 'ACT-0002', title: 'Add DLP rule for external recipients with customer data', sourceType: 'incident', sourceId: incIds[1]!, ownerId: owner.ciso, dueAt: d(-1), status: 'open' },
      { ref: 'ACT-0003', title: 'Complete EDD remediation for 14 corporate files', sourceType: 'finding', sourceId: 1, ownerId: owner.co, dueAt: d(10), status: 'in_progress' },
      { ref: 'ACT-0004', title: 'Update mail filtering rules', sourceType: 'incident', sourceId: incIds[3]!, ownerId: owner.ciso, dueAt: d(-15), status: 'done', completedAt: d(-14), evidenceNote: 'Change CR-4471 deployed' },
    ]).run()

    // ── ESG (Module J) ──
    tx.insert(t.esgMetrics).values([
      { framework: 'TCFD', pillar: 'E', name: 'Scope 1 & 2 emissions', unit: 'tCO2e', value: 1840, target: 1700, period: 'FY2026', status: 'at_risk', ownerId: owner.risk },
      { framework: 'TCFD', pillar: 'E', name: 'Financed emissions coverage', unit: '%', value: 62, target: 80, lowerIsBetter: false, period: 'FY2026', status: 'off_track', ownerId: owner.risk },
      { framework: 'CSRD', pillar: 'E', name: 'Renewable electricity share', unit: '%', value: 78, target: 75, lowerIsBetter: false, period: 'FY2026', status: 'on_track', ownerId: owner.risk },
      { framework: 'CSRD', pillar: 'S', name: 'Gender pay gap (median)', unit: '%', value: 14.2, target: 12, period: 'FY2026', status: 'at_risk', ownerId: owner.hr },
      { framework: 'GRI', pillar: 'S', name: 'Mandatory training completion', unit: '%', value: 96, target: 95, lowerIsBetter: false, period: 'FY2026', status: 'on_track', ownerId: owner.hr },
      { framework: 'GRI', pillar: 'S', name: 'Suppliers with modern slavery attestation', unit: '%', value: 81, target: 100, lowerIsBetter: false, period: 'FY2026', status: 'off_track', ownerId: owner.comp },
      { framework: 'GRI', pillar: 'G', name: 'Board independence', unit: '%', value: 58, target: 50, lowerIsBetter: false, period: 'FY2026', status: 'on_track', ownerId: owner.comp },
      { framework: 'CSRD', pillar: 'G', name: 'Confirmed bribery incidents', unit: '', value: 0, target: 0, period: 'FY2026', status: 'on_track', ownerId: owner.comp },
      { framework: 'TCFD', pillar: 'G', name: 'Climate risk board attestation', unit: '', value: null, target: null, period: 'FY2026', status: 'no_data', ownerId: owner.risk },
    ]).run()

    // ── Implementation programme (§7: 6 phases, 26 weeks, the doc's "27 weeks" heading conflicts with its own table) ──
    const phases = [
      [1, 'Initiation', 1, 3, 'Programme mobilised, executive sponsorship, governance, DPIA, environments provisioned', 'Steering committee sign-off', 'complete', ['Executive sponsor confirmed', 'Programme governance established', 'DPIA completed (UK GDPR Art. 35)', 'Environments provisioned']],
      [2, 'Discovery & Assessment', 4, 6, 'GRC gap analysis, regulatory mapping, system inventory, data quality assessment', 'Gap analysis accepted', 'complete', ['GRC gap analysis', 'Regulatory mapping finalised', 'System inventory documented', 'Data quality assessment']],
      [3, 'Design & Architecture', 7, 10, 'Solution design, control library, CCM rules, integrations, dashboards, security architecture', 'Design authority sign-off', 'complete', ['Control library defined', 'CCM rules designed', 'Integrations specified', 'Security architecture signed off']],
      [4, 'Build & Configure', 11, 18, 'Platform build, integrations unit-tested, dashboards, automation and workflows', 'Build complete', 'in_progress', ['CCM engine & 12 default rules', 'Executive dashboard', 'Core banking stream integration', 'Workday integration', 'SAR workflow', 'Board pack automation']],
      [5, 'Testing & QA', 19, 22, 'SIT, UAT, penetration test, performance and compliance, then a go/no-go decision', 'Go/No-Go', 'not_started', ['SIT complete (>99% data quality)', 'UAT passed', 'CREST penetration test', 'Performance validated']],
      [6, 'Implementation & Training', 23, 26, 'Phased rollout, data migration, training, parallel run, BAU handover', 'BAU handover', 'not_started', ['Data migrated', 'All users trained', 'Parallel run', 'BAU handover']],
    ] as const
    for (const [id, name, startWeek, endWeek, scope, gate, status, tasks] of phases) {
      tx.insert(t.programmePhases).values({ id, name, startWeek, endWeek, scope, gate, status, signedOffAt: status === 'complete' ? d(-100 + id * 20) : null }).run()
      tasks.forEach((title, i) => {
        const done = status === 'complete' || (status === 'in_progress' && i < 3)
        tx.insert(t.programmeTasks).values({ phaseId: id, title, done, doneAt: done ? d(-30 + i) : null }).run()
      })
    }

    // ── Historical (closed) alerts so the 14-day trend has context ──
    const histRules = DEFAULT_RULES
    let refN = 1
    for (let day = 13; day >= 1; day--) {
      const count = 2 + Math.floor(rnd() * 5)
      for (let k = 0; k < count; k++) {
        const rule = pick(histRules)
        const detected = new Date(now - day * DAY - rnd() * DAY * 0.8)
        tx.insert(t.alerts).values({
          ref: `ALT-${String(refN++).padStart(6, '0')}`, source: 'ccm', ruleId: rule.id, dedupeKey: `hist:${day}:${k}`, title: `${rule.name} (resolved)`,
          detail: 'Historical alert', module: rule.module, severity: rule.severity, status: 'closed', escalationPath: rule.escalationPath, sla: rule.sla,
          detectedAt: detected, ackDueAt: new Date(detected.getTime() + 30 * 60_000), acknowledgedAt: new Date(detected.getTime() + 20 * 60_000),
          dueAt: new Date(detected.getTime() + 4 * HOUR), closedAt: new Date(detected.getTime() + 3 * HOUR), closedById: owner.comp,
          closureEvidence: 'Remediated; evidence on file.', falsePositive: rnd() < 0.12,
        }).run()
      }
    }
  })
}
