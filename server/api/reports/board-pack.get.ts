import { and, gte, inArray, isNull, lte, sql } from 'drizzle-orm'

/**
 * Board pack (§1.2 "board-level reporting packs", §4.6 step 8). Figures come straight from the
 * platform; the narrative is generated deterministically from those figures so every sentence is
 * traceable to a number in the pack. (An LLM summariser can be layered on later, but the pack must
 * never contain a claim that the data does not support.)
 */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'reports:board')
  const db = useDb()
  const t = tables
  const now = new Date()
  const since = new Date(now.getTime() - 30 * 86_400_000)

  const open = db.select().from(t.alerts).where(inArray(t.alerts.status, [...OPEN_STATUSES])).all()
  const raised30 = db.select({ n: sql<number>`count(*)` }).from(t.alerts).where(gte(t.alerts.detectedAt, since)).get()?.n ?? 0
  const closed30 = db.select({ detectedAt: t.alerts.detectedAt, closedAt: t.alerts.closedAt, fp: t.alerts.falsePositive }).from(t.alerts)
    .where(and(gte(t.alerts.closedAt, since))).all()
  const mttrHours = closed30.length ? Math.round(10 * closed30.reduce((s, a) => s + (a.closedAt!.getTime() - a.detectedAt.getTime()) / 3_600_000, 0) / closed30.length) / 10 : null
  const rules = db.select().from(t.ccmRules).all()
  const register = riskRegister()
  const topRisks = [...register].sort((a, b) => b.residual.score - a.residual.score).slice(0, 6)
  const obligations = db.select({ status: t.obligations.status, framework: t.obligations.framework, title: t.obligations.title, ref: t.obligations.ref }).from(t.obligations).all()
  const reports = db.select().from(t.regulatoryReports).where(and(isNull(t.regulatoryReports.submittedAt), lte(t.regulatoryReports.dueAt, new Date(now.getTime() + 30 * 86_400_000)))).all()
  const findings = db.select().from(t.auditFindings).all()
  const incidents = db.select().from(t.incidents).where(gte(t.incidents.reportedAt, since)).all()
  const assuranceActs = db.select().from(t.assuranceActivities).all()
  const aml = db.select({ status: t.amlCases.status, sarSubmittedAt: t.amlCases.sarSubmittedAt }).from(t.amlCases).all()

  const overdueAlerts = open.filter(a => a.overdue)
  const failingRules = rules.filter(r => r.state === 'fail')
  const nonCompliant = obligations.filter(o => o.status === 'non_compliant')
  const overdueFindings = findings.filter(f => f.status === 'open' && f.dueAt < now)

  const narrative: string[] = []
  narrative.push(`${open.length} control alerts are open, of which ${open.filter(a => a.severity === 'critical').length} are critical and ${overdueAlerts.length} are past their resolution SLA. ${raised30} alerts were raised in the last 30 days${mttrHours !== null ? `, with a mean time to close of ${mttrHours} hours` : ''}.`)
  narrative.push(`${rules.length - failingRules.length} of ${rules.length} continuous monitoring rules are currently passing or within tolerance.${failingRules.length ? ` Rules in breach: ${failingRules.map(r => `${r.id} ${r.name}`).join('; ')}.` : ''}`)
  if (topRisks[0]) narrative.push(`The highest residual risk is ${topRisks[0].ref} "${topRisks[0].title}" (residual ${topRisks[0].residual.score}/25, inherent ${topRisks[0].inherent.score}/25). ${register.filter(r => r.controlGap).length} open risks have no mapped controls.`)
  narrative.push(`${obligations.filter(o => o.status === 'compliant').length} of ${obligations.length} regulatory obligations are assessed as compliant${nonCompliant.length ? `; non-compliant: ${nonCompliant.map(o => o.title).join('; ')}` : ''}. ${reports.length} regulatory return(s) fall due in the next 30 days.`)
  narrative.push(`Internal audit has ${findings.filter(f => f.status === 'open').length} open findings, ${overdueFindings.length} overdue. ${incidents.length} incident(s) were logged in the last 30 days, ${incidents.filter(i => i.severity === 'critical' || i.severity === 'high').length} rated high or critical.`)

  writeAudit(actorOf(user), 'report.board_pack_generated', 'report', null, {}, event)
  return {
    generatedAt: now.toISOString(),
    generatedBy: user.name,
    narrative,
    kpis: {
      openAlerts: open.length,
      criticalAlerts: open.filter(a => a.severity === 'critical').length,
      overdueAlerts: overdueAlerts.length,
      mttrHours,
      falsePositiveRate: closed30.length ? Math.round(100 * closed30.filter(a => a.fp).length / closed30.length) : null,
      rulesPassing: rules.filter(r => r.state === 'pass').length,
      rulesTotal: rules.length,
      complianceRate: obligations.length ? Math.round(100 * obligations.filter(o => o.status === 'compliant').length / obligations.length) : null,
      sarsSubmitted30d: aml.filter(c => c.sarSubmittedAt && c.sarSubmittedAt >= since).length,
    },
    topRisks: topRisks.map(r => ({ ref: r.ref, title: r.title, category: r.category, inherent: r.inherent.score, residual: r.residual.score, band: r.residual.band, owner: r.ownerName, treatment: r.treatment })),
    failingRules: failingRules.map(r => ({ id: r.id, name: r.name, breaches: r.lastBreaches })),
    overdueAlerts: overdueAlerts.slice(0, 10).map(a => ({ ref: a.ref, title: a.title, severity: a.severity, detectedAt: a.detectedAt })),
    nonCompliant: nonCompliant.map(o => ({ ref: o.ref, title: o.title, framework: o.framework })),
    reportsDue: reports.map(r => ({ name: r.name, regulator: r.regulator, dueAt: r.dueAt })),
    assurance: ([1, 2, 3] as const).map(line => ({ line, complete: assuranceActs.filter(a => a.line === line && a.status === 'complete').length, total: assuranceActs.filter(a => a.line === line).length })),
  }
})
