import { desc, eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'aml:read')
  const { amlCases, customers } = tables
  const rows = useDb().select({ c: amlCases, customer: { id: customers.id, ref: customers.ref, name: customers.name, riskRating: customers.riskRating, country: customers.country } })
    .from(amlCases).innerJoin(customers, eq(customers.id, amlCases.customerId)).orderBy(desc(amlCases.openedAt)).all()
  const names = userNames(rows.map(r => r.c.analystId))
  const now = Date.now()
  return rows.map(({ c, customer }) => ({
    ...c,
    customer: maskPii([customer], user.role)[0]!,
    analystName: c.analystId ? names.get(c.analystId) ?? null : null,
    sarDaysElapsed: c.sarDecisionAt && !c.sarSubmittedAt ? Math.floor((now - c.sarDecisionAt.getTime()) / 86_400_000) : null,
  }))
})
