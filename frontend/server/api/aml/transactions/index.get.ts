import { desc, eq, gte } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'aml:read')
  const q = getQuery(event)
  const { transactions: tx, customers } = tables
  const rows = useDb().select({ tx, customer: { id: customers.id, ref: customers.ref, name: customers.name } })
    .from(tx).innerJoin(customers, eq(customers.id, tx.customerId))
    .where(q.pending ? eq(tx.screeningStatus, 'pending') : gte(tx.at, new Date(Date.now() - 2 * 86_400_000)))
    .orderBy(desc(tx.at)).limit(q.pending ? 100 : 60).all()
  return rows.map(r => ({ ...r.tx, customer: maskPii([r.customer], user.role)[0]!, counterpartyRisk: countryRiskOf(r.tx.counterpartyCountry) }))
})
