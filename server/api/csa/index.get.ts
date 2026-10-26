import { desc, eq } from 'drizzle-orm'

/** Control Self-Assessment portal (1st line, §4.6 step 4). BU managers see only their unit. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'csa:read')
  const { csaAssessments: csa, controls } = tables
  const rows = useDb().select({ csa, control: { ref: controls.ref, name: controls.name, effectiveness: controls.effectiveness, frequency: controls.frequency } })
    .from(csa).innerJoin(controls, eq(controls.id, csa.controlId))
    .where(buScope(user, csa.businessUnit)).orderBy(desc(csa.dueAt)).all()
  const names = userNames(rows.map(r => r.csa.submittedById))
  const now = Date.now()
  return rows.map(({ csa: a, control }) => ({
    ...a,
    control,
    submittedByName: a.submittedById ? names.get(a.submittedById) ?? null : null,
    overdue: !a.submittedAt && a.dueAt.getTime() < now,
    // Independence check: self-assessment disagrees with test-derived effectiveness.
    divergent: !!a.result && control.effectiveness !== 'not_tested' && a.result !== control.effectiveness,
  }))
})
