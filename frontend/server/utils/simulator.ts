import { and, eq, inArray, lt, sql } from 'drizzle-orm'

// DEMO ONLY (runtimeConfig.demoSimulator). Stands in for the live source-system connectors of §2.4
// so the CCM engine has a realistic stream to evaluate. Disable in any real deployment: the
// production path is the integration layer writing to the same tables.

const COUNTERPARTIES = [['Acme Supplies Ltd', 'GB'], ['Rhine Metals GmbH', 'DE'], ['Dublin Freight', 'IE'], ['Atlas Trading LLC', 'AE'], ['Coral Bay Holdings', 'VG']] as const
const pick = <T>(a: readonly T[]) => a[Math.floor(Math.random() * a.length)]!

export function simulateTick(now = new Date()) {
  const db = useDb()
  const { customers, transactions, controls, controlTests, integrations } = tables

  const verified = db.select({ id: customers.id, type: customers.type }).from(customers).where(eq(customers.kycStatus, 'verified')).all()
  if (!verified.length) return

  // Screening service clears pending low-value payments from previous ticks; high-value ones wait
  // for an analyst decision (that is what CCM-002 watches).
  const cleared = db.update(transactions).set({ screeningStatus: 'clear' })
    .where(and(eq(transactions.screeningStatus, 'pending'), lt(transactions.amount, 10_000), lt(transactions.at, new Date(now.getTime() - 30_000)))).run().changes

  const last = db.select({ max: sql<number>`count(*)` }).from(transactions).get()?.max ?? 0
  const batch = 2 + Math.floor(Math.random() * 5)
  const rows: typeof transactions.$inferInsert[] = []
  for (let i = 0; i < batch; i++) {
    const c = pick(verified)
    const highValue = Math.random() < 0.04
    const [cp, cc] = pick(COUNTERPARTIES)
    rows.push({
      ref: `TX-S${String(last + i + 1).padStart(7, '0')}`,
      customerId: c.id,
      amount: highValue ? 25_000 + Math.round(Math.random() * 200_000) : Math.round((c.type === 'entity' ? 4000 : 400) * (0.3 + Math.random())),
      currency: 'GBP',
      direction: Math.random() < 0.55 ? 'out' : 'in',
      counterparty: cp,
      counterpartyCountry: cc,
      at: now,
      screeningStatus: 'pending',
    })
  }
  db.insert(transactions).values(rows).run()

  // Occasional automated control test (feeds CCM-012).
  if (Math.random() < 0.15) {
    const automated = db.select({ id: controls.id }).from(controls).where(eq(controls.automated, true)).all()
    const c = pick(automated)
    const result = Math.random() < 0.08 ? 'fail' : 'pass'
    db.insert(controlTests).values({ controlId: c.id, at: now, result, method: 'automated', notes: result === 'fail' ? 'Automated test exception (simulated)' : null }).run()
    recomputeEffectiveness(c.id)
  }

  db.update(integrations).set({ lastSyncAt: now, recordsIngested: sql`${integrations.recordsIngested} + ${batch}` })
    .where(inArray(integrations.id, ['core-banking', 'lseg'])).run()

  publish({ type: 'data', payload: { domain: 'transactions' } })
  return { inserted: batch, cleared }
}
