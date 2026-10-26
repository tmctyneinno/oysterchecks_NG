// Data model for the OysterChecks GRC platform (spec §3, 11 modules sharing one data model).
// SQLite via Drizzle for development; the schema avoids SQLite-only features so it can move to Postgres.
import { sql } from 'drizzle-orm'
import { index, integer, real, sqliteTable, text, uniqueIndex } from 'drizzle-orm/sqlite-core'
import type { RoleKey } from '../../shared/utils/rbac'
import type { ControlEffectiveness } from '../../shared/utils/risk'
import type { EscalationTarget, Severity, SlaSpec } from '../../shared/utils/sla'

const ts = (name: string) => integer(name, { mode: 'timestamp_ms' })
const createdAt = () => ts('created_at').notNull().default(sql`(unixepoch() * 1000)`)
const bool = (name: string) => integer(name, { mode: 'boolean' })

// ── Identity & platform ─────────────────────────────────────────────────────

export const users = sqliteTable('users', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  email: text('email').notNull().unique(),
  name: text('name').notNull(),
  role: text('role').$type<RoleKey>().notNull(),
  businessUnit: text('business_unit'),
  passwordHash: text('password_hash').notNull(),
  mfaSecret: text('mfa_secret'), // AES-256-GCM encrypted
  mfaEnabled: bool('mfa_enabled').notNull().default(false),
  failedLogins: integer('failed_logins').notNull().default(0),
  lockedUntil: ts('locked_until'),
  active: bool('active').notNull().default(true),
  lastLoginAt: ts('last_login_at'),
  createdAt: createdAt(),
})

/** Append-only, hash-chained (§2.3 "Immutable audit log"). UPDATE/DELETE are blocked by triggers. */
export const auditLog = sqliteTable('audit_log', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  at: ts('at').notNull(),
  actorId: integer('actor_id'),
  actorName: text('actor_name').notNull(),
  action: text('action').notNull(),
  entityType: text('entity_type').notNull(),
  entityId: text('entity_id'),
  detail: text('detail', { mode: 'json' }).$type<Record<string, unknown>>(),
  ip: text('ip'),
  prevHash: text('prev_hash').notNull(),
  hash: text('hash').notNull(),
}, t => [index('audit_entity_idx').on(t.entityType, t.entityId)])

export const notifications = sqliteTable('notifications', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  userId: integer('user_id').notNull(),
  at: ts('at').notNull(),
  severity: text('severity').$type<Severity | 'info'>().notNull(),
  title: text('title').notNull(),
  body: text('body'),
  link: text('link'),
  readAt: ts('read_at'),
}, t => [index('notif_user_idx').on(t.userId, t.readAt)])

/** Generic traceability links between any two records (policy↔risk, risk↔control, incident↔control…). */
export const links = sqliteTable('links', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  fromType: text('from_type').notNull(),
  fromId: integer('from_id').notNull(),
  toType: text('to_type').notNull(),
  toId: integer('to_id').notNull(),
}, t => [uniqueIndex('links_unique').on(t.fromType, t.fromId, t.toType, t.toId)])

export const evidence = sqliteTable('evidence', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  entityType: text('entity_type').notNull(),
  entityId: integer('entity_id').notNull(),
  filename: text('filename').notNull(),
  mime: text('mime').notNull(),
  size: integer('size').notNull(),
  sha256: text('sha256').notNull(),
  storageKey: text('storage_key').notNull(),
  uploadedById: integer('uploaded_by_id').notNull(),
  uploadedAt: ts('uploaded_at').notNull(),
}, t => [index('evidence_entity_idx').on(t.entityType, t.entityId)])

export const integrations = sqliteTable('integrations', {
  id: text('id').primaryKey(),
  name: text('name').notNull(),
  method: text('method').notNull(),
  direction: text('direction').notNull(),
  frequency: text('frequency').notNull(),
  dataElements: text('data_elements').notNull(),
  status: text('status').$type<'connected' | 'degraded' | 'disconnected' | 'simulated'>().notNull(),
  lastSyncAt: ts('last_sync_at'),
  recordsIngested: integer('records_ingested').notNull().default(0),
  dataQuality: real('data_quality'),
})

// ── A. Governance ───────────────────────────────────────────────────────────

export const policies = sqliteTable('policies', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  title: text('title').notNull(),
  summary: text('summary'),
  ownerId: integer('owner_id').notNull(),
  version: integer('version').notNull().default(1),
  status: text('status').$type<'draft' | 'in_review' | 'approved' | 'retired'>().notNull(),
  jurisdiction: text('jurisdiction').notNull(),
  frameworks: text('frameworks', { mode: 'json' }).$type<string[]>().notNull(),
  reviewDate: ts('review_date').notNull(),
  approvedById: integer('approved_by_id'),
  approvedAt: ts('approved_at'),
  createdAt: createdAt(),
})

export const policyVersions = sqliteTable('policy_versions', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  policyId: integer('policy_id').notNull(),
  version: integer('version').notNull(),
  status: text('status').notNull(),
  note: text('note'),
  actorId: integer('actor_id').notNull(),
  at: ts('at').notNull(),
})

export const policyAcknowledgements = sqliteTable('policy_acknowledgements', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  policyId: integer('policy_id').notNull(),
  userId: integer('user_id').notNull(),
  version: integer('version').notNull(),
  at: ts('at').notNull(),
}, t => [uniqueIndex('ack_unique').on(t.policyId, t.userId, t.version)])

// ── B. Enterprise Risk Management ───────────────────────────────────────────

export const risks = sqliteTable('risks', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  title: text('title').notNull(),
  description: text('description'),
  category: text('category').notNull(),
  ownerId: integer('owner_id').notNull(),
  businessUnit: text('business_unit'),
  impact: integer('impact').notNull(),
  likelihood: integer('likelihood').notNull(),
  treatment: text('treatment').$type<'accept' | 'mitigate' | 'transfer' | 'avoid'>().notNull(),
  treatmentPlan: text('treatment_plan'),
  treatmentDueAt: ts('treatment_due_at'),
  source: text('source').$type<'manual' | 'ai' | 'incident' | 'audit' | 'threat_intel'>().notNull().default('manual'),
  status: text('status').$type<'open' | 'closed'>().notNull().default('open'),
  escalatedToBoard: bool('escalated_to_board').notNull().default(false),
  lastReviewedAt: ts('last_reviewed_at'),
  createdAt: createdAt(),
})

export const kris = sqliteTable('kris', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  riskId: integer('risk_id').notNull(),
  name: text('name').notNull(),
  unit: text('unit').notNull(),
  value: real('value').notNull(),
  amber: real('amber').notNull(),
  red: real('red').notNull(),
  history: text('history', { mode: 'json' }).$type<number[]>().notNull(),
  updatedAt: ts('updated_at').notNull(),
})

// ── D. Controls Library ─────────────────────────────────────────────────────

export const controls = sqliteTable('controls', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  name: text('name').notNull(),
  description: text('description'),
  ownerId: integer('owner_id').notNull(),
  businessUnit: text('business_unit').notNull(),
  type: text('type').$type<'preventive' | 'detective' | 'corrective'>().notNull(),
  frequency: text('frequency').$type<'continuous' | 'daily' | 'weekly' | 'monthly' | 'quarterly' | 'annual'>().notNull(),
  automated: bool('automated').notNull(),
  keyControl: bool('key_control').notNull().default(true),
  effectiveness: text('effectiveness').$type<ControlEffectiveness>().notNull().default('not_tested'),
  ccmRuleId: text('ccm_rule_id'),
  lastTestedAt: ts('last_tested_at'),
  createdAt: createdAt(),
})

export const controlTests = sqliteTable('control_tests', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  controlId: integer('control_id').notNull(),
  at: ts('at').notNull(),
  result: text('result').$type<'pass' | 'fail'>().notNull(),
  method: text('method').$type<'automated' | 'manual'>().notNull(),
  testerId: integer('tester_id'),
  notes: text('notes'),
}, t => [index('control_tests_idx').on(t.controlId, t.at)])

// ── C. Compliance Obligations & regulatory reporting ────────────────────────

export const obligations = sqliteTable('obligations', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  title: text('title').notNull(),
  sourceType: text('source_type').$type<'law' | 'regulation' | 'standard' | 'internal_policy'>().notNull(),
  framework: text('framework').notNull(),
  regulator: text('regulator'),
  jurisdiction: text('jurisdiction').notNull(),
  businessUnit: text('business_unit'),
  ownerId: integer('owner_id').notNull(),
  status: text('status').$type<'compliant' | 'partial' | 'non_compliant' | 'not_assessed'>().notNull(),
  lastAssessedAt: ts('last_assessed_at'),
  nextReviewAt: ts('next_review_at'),
})

export const regulatoryReports = sqliteTable('regulatory_reports', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  name: text('name').notNull(),
  regulator: text('regulator').notNull(),
  frequency: text('frequency').notNull(),
  dueAt: ts('due_at').notNull(),
  automated: bool('automated').notNull(),
  preparedAt: ts('prepared_at'),
  submittedAt: ts('submitted_at'),
  submittedById: integer('submitted_by_id'),
  reference: text('reference'),
  ownerId: integer('owner_id').notNull(),
})

// ── E. Combined Assurance / CSA ─────────────────────────────────────────────

export const assuranceActivities = sqliteTable('assurance_activities', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  line: integer('line').$type<1 | 2 | 3>().notNull(),
  provider: text('provider').$type<'management' | 'compliance' | 'risk' | 'internal_audit' | 'external_audit' | 'regulator'>().notNull(),
  title: text('title').notNull(),
  activityType: text('activity_type').$type<'csa' | 'monitoring' | 'testing' | 'audit' | 'review'>().notNull(),
  plannedStart: ts('planned_start').notNull(),
  plannedEnd: ts('planned_end').notNull(),
  status: text('status').$type<'planned' | 'in_progress' | 'complete'>().notNull(),
  rating: text('rating').$type<'satisfactory' | 'needs_improvement' | 'unsatisfactory'>(),
  ownerId: integer('owner_id').notNull(),
})

export const csaAssessments = sqliteTable('csa_assessments', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  controlId: integer('control_id').notNull(),
  businessUnit: text('business_unit').notNull(),
  period: text('period').notNull(),
  dueAt: ts('due_at').notNull(),
  submittedAt: ts('submitted_at'),
  submittedById: integer('submitted_by_id'),
  result: text('result').$type<ControlEffectiveness>(),
  comment: text('comment'),
})

// ── F. Audit & Assurance Management ─────────────────────────────────────────

export const auditEngagements = sqliteTable('audit_engagements', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  title: text('title').notNull(),
  scope: text('scope'),
  businessUnit: text('business_unit').notNull(),
  leadId: integer('lead_id').notNull(),
  status: text('status').$type<'planned' | 'fieldwork' | 'reporting' | 'closed'>().notNull(),
  plannedStart: ts('planned_start').notNull(),
  plannedEnd: ts('planned_end').notNull(),
  rating: text('rating').$type<'satisfactory' | 'needs_improvement' | 'unsatisfactory'>(),
})

export const auditFindings = sqliteTable('audit_findings', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  engagementId: integer('engagement_id').notNull(),
  title: text('title').notNull(),
  rating: text('rating').$type<'high' | 'medium' | 'low'>().notNull(),
  ownerId: integer('owner_id').notNull(),
  businessUnit: text('business_unit').notNull(),
  managementResponse: text('management_response'),
  dueAt: ts('due_at').notNull(),
  status: text('status').$type<'open' | 'closed'>().notNull(),
  closedAt: ts('closed_at'),
  closureEvidence: text('closure_evidence'),
})

// ── G. Financial Crime & AML ────────────────────────────────────────────────

export const customers = sqliteTable('customers', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  name: text('name').notNull(),
  type: text('type').$type<'individual' | 'entity'>().notNull(),
  dob: text('dob'),
  country: text('country').notNull(),
  countryRisk: text('country_risk').$type<'low' | 'medium' | 'high'>().notNull(),
  businessType: text('business_type'),
  riskRating: text('risk_rating').$type<'standard' | 'enhanced' | 'high'>().notNull(),
  kycStatus: text('kyc_status').$type<'pending' | 'verified' | 'failed'>().notNull(),
  kycVerifiedAt: ts('kyc_verified_at'),
  pep: bool('pep').notNull().default(false),
  sanctionsStatus: text('sanctions_status').$type<'pending' | 'clear' | 'potential_match' | 'confirmed_match'>().notNull(),
  adverseMedia: bool('adverse_media').notNull().default(false),
  eddStatus: text('edd_status').$type<'not_required' | 'required' | 'in_progress' | 'complete'>().notNull(),
  eddCompletedAt: ts('edd_completed_at'),
  businessUnit: text('business_unit').notNull(),
  createdAt: ts('created_at').notNull(),
})

export const transactions = sqliteTable('transactions', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  customerId: integer('customer_id').notNull(),
  amount: real('amount').notNull(),
  currency: text('currency').notNull(),
  direction: text('direction').$type<'in' | 'out'>().notNull(),
  counterparty: text('counterparty').notNull(),
  counterpartyCountry: text('counterparty_country').notNull(),
  at: ts('at').notNull(),
  screeningStatus: text('screening_status').$type<'pending' | 'clear' | 'hit'>().notNull(),
}, t => [index('tx_customer_at_idx').on(t.customerId, t.at)])

export const amlCases = sqliteTable('aml_cases', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  customerId: integer('customer_id').notNull(),
  transactionId: integer('transaction_id'),
  alertId: integer('alert_id'),
  type: text('type').$type<'transaction' | 'sanctions' | 'kyc' | 'edd'>().notNull(),
  score: integer('score').notNull(),
  scoreFactors: text('score_factors', { mode: 'json' }).$type<{ factor: string, weight: number }[]>().notNull(),
  status: text('status').$type<'new' | 'under_review' | 'escalated' | 'sar_decided' | 'sar_submitted' | 'closed'>().notNull(),
  analystId: integer('analyst_id'),
  openedAt: ts('opened_at').notNull(),
  sarDecisionAt: ts('sar_decision_at'),
  sarDraft: text('sar_draft'),
  sarSubmittedAt: ts('sar_submitted_at'),
  ncaReference: text('nca_reference'),
  outcome: text('outcome'),
  closedAt: ts('closed_at'),
})

// ── HR vetting & security (CCM-006, CCM-007) ────────────────────────────────

export const employees = sqliteTable('employees', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  name: text('name').notNull(),
  dob: text('dob'),
  department: text('department').notNull(),
  jobTitle: text('job_title').notNull(),
  businessUnit: text('business_unit').notNull(),
  startDate: ts('start_date').notNull(),
  vettingType: text('vetting_type').$type<'BPSS' | 'DBS' | 'BS7858'>().notNull(),
  vettingStatus: text('vetting_status').$type<'not_started' | 'in_progress' | 'cleared' | 'failed'>().notNull(),
  vettingClearedAt: ts('vetting_cleared_at'),
  terminatedAt: ts('terminated_at'),
})

export const accessAccounts = sqliteTable('access_accounts', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  account: text('account').notNull(),
  system: text('system').notNull(),
  holder: text('holder').notNull(),
  privileged: bool('privileged').notNull(),
  groups: text('groups', { mode: 'json' }).$type<string[]>().notNull(),
  lastReviewedAt: ts('last_reviewed_at'),
  reviewedById: integer('reviewed_by_id'),
})

// ── H. Third-Party Risk ─────────────────────────────────────────────────────

export const suppliers = sqliteTable('suppliers', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  name: text('name').notNull(),
  services: text('services').notNull(),
  ownerId: integer('owner_id').notNull(),
  country: text('country').notNull(),
  tier: text('tier').$type<'critical' | 'high' | 'medium' | 'low'>().notNull(),
  dataAccess: bool('data_access').notNull(),
  annualSpend: real('annual_spend').notNull(),
  ddStatus: text('dd_status').$type<'pending' | 'in_progress' | 'complete'>().notNull(),
  questionnaireScore: integer('questionnaire_score'),
  sanctionsStatus: text('sanctions_status').$type<'pending' | 'clear' | 'potential_match' | 'confirmed_match'>().notNull(),
  monitoringFlags: text('monitoring_flags', { mode: 'json' }).$type<string[]>().notNull(),
  status: text('status').$type<'onboarding' | 'active' | 'exited'>().notNull(),
  lastReviewAt: ts('last_review_at'),
  nextReviewAt: ts('next_review_at'),
  contractRenewalAt: ts('contract_renewal_at'),
  dpaInPlace: bool('dpa_in_place').notNull().default(false),
})

// ── I. Incidents, issues & corrective actions ───────────────────────────────

export const incidents = sqliteTable('incidents', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  title: text('title').notNull(),
  description: text('description'),
  type: text('type').$type<'operational' | 'data' | 'financial_crime' | 'regulatory' | 'audit_finding' | 'cyber'>().notNull(),
  severity: text('severity').$type<Severity>().notNull(),
  status: text('status').$type<'open' | 'investigating' | 'remediating' | 'closed'>().notNull(),
  businessUnit: text('business_unit').notNull(),
  reportedById: integer('reported_by_id').notNull(),
  ownerId: integer('owner_id'),
  occurredAt: ts('occurred_at').notNull(),
  reportedAt: ts('reported_at').notNull(),
  rcaMethod: text('rca_method').$type<'5_whys' | 'ishikawa'>(),
  rootCause: text('root_cause'),
  notifiable: bool('notifiable').notNull().default(false),
  regulator: text('regulator'),
  notificationDueAt: ts('notification_due_at'),
  notifiedAt: ts('notified_at'),
  lessonsLearned: text('lessons_learned'),
  sourceAlertId: integer('source_alert_id'),
  closedAt: ts('closed_at'),
  closedById: integer('closed_by_id'),
})

export const actions = sqliteTable('actions', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  title: text('title').notNull(),
  sourceType: text('source_type').$type<'incident' | 'finding' | 'alert' | 'risk'>().notNull(),
  sourceId: integer('source_id').notNull(),
  ownerId: integer('owner_id').notNull(),
  dueAt: ts('due_at').notNull(),
  status: text('status').$type<'open' | 'in_progress' | 'done'>().notNull(),
  completedAt: ts('completed_at'),
  evidenceNote: text('evidence_note'),
})

// ── J. ESG ──────────────────────────────────────────────────────────────────

export const esgMetrics = sqliteTable('esg_metrics', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  framework: text('framework').$type<'TCFD' | 'CSRD' | 'GRI'>().notNull(),
  pillar: text('pillar').$type<'E' | 'S' | 'G'>().notNull(),
  name: text('name').notNull(),
  unit: text('unit').notNull(),
  value: real('value'),
  target: real('target'),
  lowerIsBetter: bool('lower_is_better').notNull().default(true),
  period: text('period').notNull(),
  status: text('status').$type<'on_track' | 'at_risk' | 'off_track' | 'no_data'>().notNull(),
  evidenceNote: text('evidence_note'),
  ownerId: integer('owner_id').notNull(),
})

// ── Continuous Control Monitoring (§4.4, §5) ────────────────────────────────

export const ccmRules = sqliteTable('ccm_rules', {
  id: text('id').primaryKey(), // CCM-001 …
  name: text('name').notNull(),
  description: text('description').notNull(),
  evaluator: text('evaluator').notNull(),
  params: text('params', { mode: 'json' }).$type<Record<string, number>>().notNull(),
  severity: text('severity').$type<Severity>().notNull(),
  sla: text('sla', { mode: 'json' }).$type<SlaSpec>().notNull(),
  escalationPath: text('escalation_path', { mode: 'json' }).$type<EscalationTarget[]>().notNull(),
  module: text('module').notNull(),
  dataSource: text('data_source').notNull(),
  enabled: bool('enabled').notNull().default(true),
  state: text('state').$type<'pass' | 'warn' | 'fail' | 'unknown'>().notNull().default('unknown'),
  lastRunAt: ts('last_run_at'),
  lastBreaches: integer('last_breaches').notNull().default(0),
  lastWarnings: integer('last_warnings').notNull().default(0),
})

export const alerts = sqliteTable('alerts', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  ref: text('ref').notNull().unique(),
  source: text('source').$type<'ccm' | 'kri' | 'manual'>().notNull(),
  ruleId: text('rule_id'),
  dedupeKey: text('dedupe_key'),
  entityType: text('entity_type'),
  entityId: integer('entity_id'),
  title: text('title').notNull(),
  detail: text('detail'),
  module: text('module').notNull(),
  businessUnit: text('business_unit'),
  severity: text('severity').$type<Severity>().notNull(),
  status: text('status').$type<'open' | 'acknowledged' | 'investigating' | 'escalated' | 'pending_closure' | 'closed'>().notNull(),
  assigneeId: integer('assignee_id'),
  ownerUserId: integer('owner_user_id'),
  escalationPath: text('escalation_path', { mode: 'json' }).$type<EscalationTarget[]>().notNull(),
  escalationLevel: integer('escalation_level').notNull().default(0),
  sla: text('sla', { mode: 'json' }).$type<SlaSpec>().notNull(),
  detectedAt: ts('detected_at').notNull(),
  ackDueAt: ts('ack_due_at').notNull(),
  acknowledgedAt: ts('acknowledged_at'),
  dueAt: ts('due_at').notNull(),
  overdue: bool('overdue').notNull().default(false),
  remediationPlan: text('remediation_plan'),
  closureEvidence: text('closure_evidence'),
  closureRequestedAt: ts('closure_requested_at'),
  closedAt: ts('closed_at'),
  closedById: integer('closed_by_id'),
  falsePositive: bool('false_positive').notNull().default(false),
}, t => [
  index('alerts_status_idx').on(t.status, t.severity),
  index('alerts_dedupe_idx').on(t.dedupeKey),
])

export const alertEvents = sqliteTable('alert_events', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  alertId: integer('alert_id').notNull(),
  at: ts('at').notNull(),
  actorId: integer('actor_id'),
  actorName: text('actor_name').notNull(),
  type: text('type').notNull(),
  note: text('note'),
}, t => [index('alert_events_idx').on(t.alertId)])

// ── Implementation programme (§7, Screen 08) ────────────────────────────────

export const programmePhases = sqliteTable('programme_phases', {
  id: integer('id').primaryKey(),
  name: text('name').notNull(),
  startWeek: integer('start_week').notNull(),
  endWeek: integer('end_week').notNull(),
  scope: text('scope').notNull(),
  gate: text('gate').notNull(),
  status: text('status').$type<'not_started' | 'in_progress' | 'complete'>().notNull(),
  signedOffAt: ts('signed_off_at'),
})

export const programmeTasks = sqliteTable('programme_tasks', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  phaseId: integer('phase_id').notNull(),
  title: text('title').notNull(),
  done: bool('done').notNull().default(false),
  doneAt: ts('done_at'),
})
