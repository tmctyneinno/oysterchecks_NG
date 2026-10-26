import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ result: z.enum(['clear', 'hit']), note: z.string().trim().min(5).max(2000) })

/** Analyst screening decision on a held payment. A hit opens a scored AML case (§4.5 steps 5–6). */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'aml:write')
  const id = parseId(event)
  const { result, note } = await validateBody(event, body)
  const db = useDb()
  const t = tables
  const tx = db.select().from(t.transactions).where(eq(t.transactions.id, id)).get()
  if (!tx) notFound('Transaction')
  if (tx.screeningStatus !== 'pending') throw createError({ statusCode: 409, statusMessage: 'Transaction already screened' })

  db.update(t.transactions).set({ screeningStatus: result }).where(eq(t.transactions.id, id)).run()
  let caseRef: string | null = null
  if (result === 'hit') {
    const customer = db.select().from(t.customers).where(eq(t.customers.id, tx.customerId)).get()!
    const { score, factors } = scoreAml({ ...customer, counterpartyCountryRisk: countryRiskOf(tx.counterpartyCountry), amount: tx.amount })
    caseRef = nextRef(t.amlCases, t.amlCases.ref, 'AML', 4)
    db.insert(t.amlCases).values({ ref: caseRef, customerId: tx.customerId, transactionId: tx.id, type: 'transaction', score, scoreFactors: factors, status: 'under_review', analystId: user.id, openedAt: new Date() }).run()
  }
  writeAudit(actorOf(user), 'transaction.screened', 'transaction', id, { ref: tx.ref, result, caseRef, note }, event)
  runCcmCycle()
  return { result, caseRef }
})
