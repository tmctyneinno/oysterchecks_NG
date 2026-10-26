import { desc } from 'drizzle-orm'

// CSV exports of registers (audit-ready evidence, §1.2). Cells are guarded against spreadsheet
// formula injection, and every export is audit-logged.

const guard = (v: unknown) => {
  if (v === null || v === undefined) return ''
  let s = v instanceof Date ? v.toISOString() : Array.isArray(v) ? v.join('; ') : String(v)
  if (/^[=+\-@\t\r]/.test(s)) s = `'${s}`
  return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s
}

const DATASETS: Record<string, { permission: Permission, columns: string[], rows: (user: { role: RoleKey, businessUnit: string | null }) => Record<string, unknown>[] }> = {
  alerts: {
    permission: 'alerts:read',
    columns: ['ref', 'ruleId', 'severity', 'status', 'title', 'module', 'businessUnit', 'detectedAt', 'acknowledgedAt', 'dueAt', 'overdue', 'closedAt', 'falsePositive'],
    rows: user => useDb().select().from(tables.alerts).orderBy(desc(tables.alerts.detectedAt)).all().filter(a => ROLES[user.role].scope !== 'bu' || a.businessUnit === user.businessUnit),
  },
  risks: {
    permission: 'risk:read',
    columns: ['ref', 'title', 'category', 'ownerName', 'impact', 'likelihood', 'inherentScore', 'residualScore', 'residualBand', 'treatment', 'controls'],
    rows: () => riskRegister().map(r => ({ ...r, inherentScore: r.inherent.score, residualScore: r.residual.score, residualBand: r.residual.band, controls: r.controls.map(c => c.ref) })),
  },
  controls: {
    permission: 'controls:read',
    columns: ['ref', 'name', 'businessUnit', 'type', 'frequency', 'automated', 'keyControl', 'effectiveness', 'ccmRuleId', 'lastTestedAt'],
    rows: user => useDb().select().from(tables.controls).all().filter(c => ROLES[user.role].scope !== 'bu' || c.businessUnit === user.businessUnit),
  },
  obligations: {
    permission: 'compliance:read',
    columns: ['ref', 'title', 'sourceType', 'framework', 'regulator', 'jurisdiction', 'status', 'lastAssessedAt', 'nextReviewAt'],
    rows: () => useDb().select().from(tables.obligations).all(),
  },
  ccm: {
    permission: 'ccm:read',
    columns: ['id', 'name', 'description', 'severity', 'state', 'enabled', 'lastRunAt', 'lastBreaches', 'lastWarnings', 'module', 'dataSource'],
    rows: () => useDb().select().from(tables.ccmRules).all(),
  },
  suppliers: {
    permission: 'tprm:read',
    columns: ['ref', 'name', 'services', 'country', 'tier', 'dataAccess', 'ddStatus', 'questionnaireScore', 'sanctionsStatus', 'status', 'nextReviewAt', 'contractRenewalAt'],
    rows: () => useDb().select().from(tables.suppliers).all(),
  },
  incidents: {
    permission: 'incidents:read',
    columns: ['ref', 'title', 'type', 'severity', 'status', 'businessUnit', 'occurredAt', 'reportedAt', 'notifiable', 'regulator', 'notificationDueAt', 'notifiedAt', 'closedAt'],
    rows: user => useDb().select().from(tables.incidents).all().filter(i => ROLES[user.role].scope !== 'bu' || i.businessUnit === user.businessUnit),
  },
}

export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const name = getRouterParam(event, 'dataset')!.replace(/\.csv$/, '')
  const ds = DATASETS[name]
  if (!ds) notFound('Dataset')
  if (!can(user.role, ds.permission)) throw createError({ statusCode: 403, statusMessage: 'Not permitted' })
  const rows = ds.rows(user)
  const csv = [ds.columns.join(','), ...rows.map(r => ds.columns.map(c => guard(r[c])).join(','))].join('\r\n')
  writeAudit(actorOf(user), 'export.csv', name, null, { rows: rows.length }, event)
  setResponseHeaders(event, {
    'content-type': 'text/csv; charset=utf-8',
    'content-disposition': `attachment; filename="oysterchecks-${name}-${new Date().toISOString().slice(0, 10)}.csv"`,
  })
  return `﻿${csv}`
})
