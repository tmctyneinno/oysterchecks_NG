import { eq, inArray } from 'drizzle-orm'

/** Risk register with inherent/residual scoring derived from mapped control effectiveness (§4.3). */
export function riskRegister() {
  const db = useDb()
  const { risks, controls, links, kris } = tables
  const rows = db.select().from(risks).where(eq(risks.status, 'open')).all()
  const riskLinks = db.select().from(links).where(eq(links.fromType, 'risk')).all().filter(l => l.toType === 'control')
  const controlIds = [...new Set(riskLinks.map(l => l.toId))]
  const controlRows = controlIds.length ? db.select().from(controls).where(inArray(controls.id, controlIds)).all() : []
  const controlById = new Map(controlRows.map(c => [c.id, c]))
  const kriRows = db.select().from(kris).all()
  const names = userNames(rows.map(r => r.ownerId))

  return rows.map((r) => {
    const mapped = riskLinks.filter(l => l.fromId === r.id).map(l => controlById.get(l.toId)).filter(c => !!c)
    const score = scoreRisk(r.impact, r.likelihood, mapped.map(c => c.effectiveness))
    return {
      ...r,
      ownerName: names.get(r.ownerId) ?? 'Unknown',
      ...score,
      controls: mapped.map(c => ({ id: c.id, ref: c.ref, name: c.name, effectiveness: c.effectiveness, ccmRuleId: c.ccmRuleId })),
      kris: kriRows.filter(k => k.riskId === r.id).map(k => ({ ...k, status: (k.value >= k.red ? 'red' : k.value >= k.amber ? 'amber' : 'green') as 'red' | 'amber' | 'green' })),
    }
  })
}
export type RiskView = ReturnType<typeof riskRegister>[number]
