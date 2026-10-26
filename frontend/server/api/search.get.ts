import { like, or } from 'drizzle-orm'

/** Global search across the registers the caller is allowed to read. */
export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const q = String(getQuery(event).q ?? '').trim()
  if (q.length < 2) return []
  const term = `%${q}%`
  const db = useDb()
  const t = tables
  const out: { domain: string, ref: string, title: string, to: string }[] = []
  const scoped = <T extends { businessUnit?: string | null }>(rows: T[]) =>
    ROLES[user.role].scope === 'bu' ? rows.filter(r => r.businessUnit === user.businessUnit) : rows

  if (can(user.role, 'alerts:read')) {
    for (const r of scoped(db.select().from(t.alerts).where(or(like(t.alerts.title, term), like(t.alerts.ref, term))).limit(6).all()))
      out.push({ domain: 'Alert', ref: r.ref, title: r.title, to: `/alerts?id=${r.id}` })
  }
  if (can(user.role, 'risk:read')) {
    for (const r of db.select().from(t.risks).where(or(like(t.risks.title, term), like(t.risks.ref, term))).limit(5).all())
      out.push({ domain: 'Risk', ref: r.ref, title: r.title, to: `/risks?id=${r.id}` })
  }
  if (can(user.role, 'controls:read')) {
    for (const r of scoped(db.select().from(t.controls).where(or(like(t.controls.name, term), like(t.controls.ref, term))).limit(5).all()))
      out.push({ domain: 'Control', ref: r.ref, title: r.name, to: `/controls?id=${r.id}` })
  }
  if (can(user.role, 'compliance:read')) {
    for (const r of db.select().from(t.obligations).where(or(like(t.obligations.title, term), like(t.obligations.framework, term))).limit(5).all())
      out.push({ domain: 'Obligation', ref: r.ref, title: r.title, to: `/compliance?id=${r.id}` })
  }
  if (can(user.role, 'governance:read')) {
    for (const r of db.select().from(t.policies).where(like(t.policies.title, term)).limit(4).all())
      out.push({ domain: 'Policy', ref: r.ref, title: r.title, to: `/governance?id=${r.id}` })
  }
  if (can(user.role, 'tprm:read')) {
    for (const r of db.select().from(t.suppliers).where(like(t.suppliers.name, term)).limit(4).all())
      out.push({ domain: 'Supplier', ref: r.ref, title: r.name, to: `/tprm?id=${r.id}` })
  }
  if (can(user.role, 'incidents:read')) {
    for (const r of scoped(db.select().from(t.incidents).where(or(like(t.incidents.title, term), like(t.incidents.ref, term))).limit(4).all()))
      out.push({ domain: 'Incident', ref: r.ref, title: r.title, to: `/incidents?id=${r.id}` })
  }
  if (can(user.role, 'aml:read')) {
    for (const r of db.select().from(t.amlCases).where(like(t.amlCases.ref, term)).limit(4).all())
      out.push({ domain: 'AML case', ref: r.ref, title: `${r.type} case`, to: `/aml?case=${r.id}` })
  }
  return out.slice(0, 20)
})
