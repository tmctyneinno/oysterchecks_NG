import { desc, eq, inArray } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'controls:read')
  const db = useDb()
  const { controls, controlTests, ccmRules, links } = tables
  const rows = db.select().from(controls).where(buScope(user, controls.businessUnit)).orderBy(controls.ref).all()
  const ids = rows.map(r => r.id)
  const tests = ids.length ? db.select().from(controlTests).where(inArray(controlTests.controlId, ids)).orderBy(desc(controlTests.at)).all() : []
  const rules = new Map(db.select({ id: ccmRules.id, state: ccmRules.state, name: ccmRules.name }).from(ccmRules).all().map(r => [r.id, r]))
  const riskLinks = db.select().from(links).where(eq(links.toType, 'control')).all().filter(l => l.fromType === 'risk')
  const assuranceLinks = db.select().from(links).where(eq(links.toType, 'control')).all().filter(l => l.fromType === 'assurance')
  const names = userNames(rows.map(r => r.ownerId))

  return rows.map(c => {
    const own = tests.filter(t => t.controlId === c.id)
    return {
      ...c,
      ownerName: names.get(c.ownerId) ?? 'Unknown',
      rule: c.ccmRuleId ? rules.get(c.ccmRuleId) ?? null : null,
      recentTests: own.slice(0, 8).map(t => ({ id: t.id, at: t.at, result: t.result, method: t.method, notes: t.notes })),
      passRate: own.length ? Math.round(100 * own.filter(t => t.result === 'pass').length / own.length) : null,
      riskIds: riskLinks.filter(l => l.toId === c.id).map(l => l.fromId),
      assuranceCount: assuranceLinks.filter(l => l.toId === c.id).length,
    }
  })
})
