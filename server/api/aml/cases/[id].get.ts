import { desc, eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'aml:read')
  const id = parseId(event)
  const db = useDb()
  const t = tables
  const c = db.select().from(t.amlCases).where(eq(t.amlCases.id, id)).get()
  if (!c) notFound('Case')
  const customer = db.select().from(t.customers).where(eq(t.customers.id, c.customerId)).get()!
  const transactions = db.select().from(t.transactions).where(eq(t.transactions.customerId, c.customerId)).orderBy(desc(t.transactions.at)).limit(25).all()
  const names = userNames([c.analystId])
  return {
    ...c,
    analystName: c.analystId ? names.get(c.analystId) ?? null : null,
    customer: maskPii([customer], user.role)[0]!,
    transactions,
    flaggedTransactionId: c.transactionId,
    evidence: listEvidence('aml_case', id),
    canSubmitSar: can(user.role, 'sar:submit'),
  }
})
