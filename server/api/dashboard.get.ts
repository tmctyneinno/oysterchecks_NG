import { and, desc, gte, inArray, isNull, lte, sql } from 'drizzle-orm'

/** Executive Intelligence Dashboard (Screen 01): everything needed to read GRC posture in one call. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'dashboard:exec')
  const db = useDb()
  const t = tables
  const now = new Date()

  const openAlerts = db.select({ severity: t.alerts.severity, overdue: t.alerts.overdue, module: t.alerts.module, status: t.alerts.status })
    .from(t.alerts).where(inArray(t.alerts.status, [...OPEN_STATUSES])).all()
  const bySeverity = Object.fromEntries(SEVERITIES.map(s => [s, openAlerts.filter(a => a.severity === s).length])) as Record<Severity, number>

  const rules = db.select().from(t.ccmRules).where(sql`${t.ccmRules.enabled} = 1`).orderBy(t.ccmRules.id).all()
  const controlRows = db.select({ effectiveness: t.controls.effectiveness, keyControl: t.controls.keyControl, ccmRuleId: t.controls.ccmRuleId }).from(t.controls).all()
  const keyControls = controlRows.filter(c => c.keyControl)

  const amlOpen = db.select({ status: t.amlCases.status }).from(t.amlCases).where(sql`${t.amlCases.status} != 'closed'`).all()
  const reports = db.select().from(t.regulatoryReports).where(and(isNull(t.regulatoryReports.submittedAt), lte(t.regulatoryReports.dueAt, new Date(now.getTime() + 30 * 86_400_000)))).all()

  const register = riskRegister()
  const categories = [...new Set(register.map(r => r.category))].map(name => ({
    name,
    count: register.filter(r => r.category === name).length,
    critical: register.filter(r => r.category === name && r.residual.band === 'critical').length,
  })).sort((a, b) => b.count - a.count)

  const activity = db.select().from(t.auditLog).where(gte(t.auditLog.at, new Date(now.getTime() - 7 * 86_400_000)))
    .orderBy(desc(t.auditLog.id)).limit(25).all()
    .filter(a => !a.action.startsWith('auth.') && !a.action.startsWith('evidence.downloaded'))

  return {
    generatedAt: now.toISOString(),
    alerts: { open: openAlerts.length, overdue: openAlerts.filter(a => a.overdue).length, pendingClosure: openAlerts.filter(a => a.status === 'pending_closure').length, bySeverity },
    trend: alertVolumeSeries(14),
    ccm: {
      total: rules.length,
      pass: rules.filter(r => r.state === 'pass').length,
      warn: rules.filter(r => r.state === 'warn').length,
      fail: rules.filter(r => r.state === 'fail').length,
      lastRunAt: rules.reduce<Date | null>((m, r) => (r.lastRunAt && (!m || r.lastRunAt > m) ? r.lastRunAt : m), null),
      coverage: keyControls.length ? Math.round(100 * keyControls.filter(c => c.ccmRuleId).length / keyControls.length) : 0,
      rules: rules.map(r => ({ id: r.id, name: r.name, state: r.state, module: r.module, breaches: r.lastBreaches })),
    },
    controls: {
      total: controlRows.length,
      effective: controlRows.filter(c => c.effectiveness === 'effective').length,
      partially: controlRows.filter(c => c.effectiveness === 'partially_effective').length,
      ineffective: controlRows.filter(c => c.effectiveness === 'ineffective').length,
      notTested: controlRows.filter(c => c.effectiveness === 'not_tested').length,
    },
    aml: { open: amlOpen.length, sarPending: amlOpen.filter(c => c.status === 'sar_decided').length },
    reports: {
      due: reports.length,
      dueSoon: reports.filter(r => r.dueAt <= new Date(now.getTime() + 7 * 86_400_000)).length,
      overdue: reports.filter(r => r.dueAt < now).length,
      next: reports.sort((a, b) => a.dueAt.getTime() - b.dueAt.getTime()).slice(0, 4).map(r => ({ id: r.id, name: r.name, regulator: r.regulator, dueAt: r.dueAt })),
    },
    risks: {
      total: register.length,
      residualBands: Object.fromEntries((['critical', 'high', 'moderate', 'low'] as const).map(b => [b, register.filter(r => r.residual.band === b).length])) as Record<RiskBand, number>,
      categories,
      top: [...register].sort((a, b) => b.residual.score - a.residual.score || b.inherent.score - a.inherent.score).slice(0, 5)
        .map(r => ({ id: r.id, ref: r.ref, title: r.title, inherent: r.inherent.score, residual: r.residual.score, band: r.residual.band, ownerName: r.ownerName })),
      controlGaps: register.filter(r => r.controlGap).length,
    },
    activity: activity.map(a => ({ id: a.id, at: a.at, actorName: a.actorName, action: a.action, entityType: a.entityType, entityId: a.entityId, detail: a.detail })),
    showFinancials: can(user.role, 'finance:read'),
  }
})
