import { eq } from 'drizzle-orm'

/**
 * Combined Assurance view (§4.6 step 7): activities by line, plus a coverage map of key controls
 * showing which lines provide assurance. Gaps (no line) and over-assurance (all three lines with
 * 4+ activities, likely duplication) are identified automatically.
 */
export default defineEventHandler(async (event) => {
  await requirePermission(event, 'assurance:read')
  const db = useDb()
  const t = tables
  const activities = db.select().from(t.assuranceActivities).orderBy(t.assuranceActivities.plannedStart).all()
  const activityLinks = db.select().from(t.links).where(eq(t.links.fromType, 'assurance')).all()
  const keyControls = db.select().from(t.controls).where(eq(t.controls.keyControl, true)).orderBy(t.controls.ref).all()
  const csa = db.select().from(t.csaAssessments).all()
  const names = userNames(activities.map(a => a.ownerId))

  const coverage = keyControls.map((c) => {
    const acts = activityLinks.filter(l => l.toType === 'control' && l.toId === c.id).map(l => activities.find(a => a.id === l.fromId)).filter(a => !!a)
    const lines = { 1: acts.some(a => a.line === 1) || csa.some(x => x.controlId === c.id), 2: acts.some(a => a.line === 2) || !!c.ccmRuleId, 3: acts.some(a => a.line === 3) }
    const count = Object.values(lines).filter(Boolean).length
    return {
      controlId: c.id, ref: c.ref, name: c.name, businessUnit: c.businessUnit, effectiveness: c.effectiveness, lines,
      activityCount: acts.length,
      status: (count === 0 ? 'gap' : count === 3 && acts.length >= 4 ? 'over' : count === 1 ? 'thin' : 'adequate') as 'gap' | 'thin' | 'adequate' | 'over',
    }
  })

  const lineSummary = ([1, 2, 3] as const).map((line) => {
    const acts = activities.filter(a => a.line === line)
    const rated = acts.filter(a => a.rating)
    return {
      line,
      total: acts.length,
      complete: acts.filter(a => a.status === 'complete').length,
      inProgress: acts.filter(a => a.status === 'in_progress').length,
      satisfactory: rated.filter(a => a.rating === 'satisfactory').length,
      rated: rated.length,
      coverage: Math.round(100 * coverage.filter(c => c.lines[line]).length / Math.max(1, coverage.length)),
    }
  })

  const csaDue = csa.filter(x => x.period === new Date().toISOString().slice(0, 7))
  return {
    activities: activities.map(a => ({ ...a, ownerName: names.get(a.ownerId) ?? 'Unknown', controlIds: activityLinks.filter(l => l.fromId === a.id).map(l => l.toId) })),
    coverage,
    lineSummary,
    csaCompletion: csaDue.length ? Math.round(100 * csaDue.filter(x => x.submittedAt).length / csaDue.length) : null,
    gaps: coverage.filter(c => c.status === 'gap').length,
    overAssured: coverage.filter(c => c.status === 'over').length,
  }
})
