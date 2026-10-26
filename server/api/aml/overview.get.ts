import { and, eq, gte, sql } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  await requirePermission(event, 'aml:read')
  const db = useDb()
  const t = tables
  const now = Date.now()
  const cases = db.select({ status: t.amlCases.status, score: t.amlCases.score, sarDecisionAt: t.amlCases.sarDecisionAt, sarSubmittedAt: t.amlCases.sarSubmittedAt }).from(t.amlCases).all()
  const customers = db.select({ kycStatus: t.customers.kycStatus, riskRating: t.customers.riskRating, eddStatus: t.customers.eddStatus, sanctionsStatus: t.customers.sanctionsStatus, pep: t.customers.pep }).from(t.customers).all()
  const since = new Date(now - 86_400_000)
  const tx24 = db.select({ n: sql<number>`count(*)`, v: sql<number>`coalesce(sum(${t.transactions.amount}),0)` }).from(t.transactions).where(gte(t.transactions.at, since)).get()
  const pendingHv = db.select({ n: sql<number>`count(*)` }).from(t.transactions).where(and(eq(t.transactions.screeningStatus, 'pending'), gte(t.transactions.amount, 10_000))).get()?.n ?? 0
  const submitted = cases.filter(c => c.sarSubmittedAt && c.sarDecisionAt)

  return {
    openCases: cases.filter(c => c.status !== 'closed').length,
    highScore: cases.filter(c => c.status !== 'closed' && c.score >= 75).length,
    pipeline: [
      { stage: 'New', key: 'new', count: cases.filter(c => c.status === 'new').length },
      { stage: 'Analyst review', key: 'under_review', count: cases.filter(c => c.status === 'under_review').length },
      { stage: 'MLRO review', key: 'escalated', count: cases.filter(c => c.status === 'escalated').length },
      { stage: 'SAR decided', key: 'sar_decided', count: cases.filter(c => c.status === 'sar_decided').length },
      { stage: 'Submitted to NCA', key: 'sar_submitted', count: cases.filter(c => c.status === 'sar_submitted').length },
    ],
    sarOverdue: cases.filter(c => c.status === 'sar_decided' && c.sarDecisionAt && now - c.sarDecisionAt.getTime() > 7 * 86_400_000).length,
    avgDaysToSubmit: submitted.length ? Math.round(10 * submitted.reduce((s, c) => s + (c.sarSubmittedAt!.getTime() - c.sarDecisionAt!.getTime()) / 86_400_000, 0) / submitted.length) / 10 : null,
    falsePositiveRate: (() => {
      const closed = db.select({ fp: t.alerts.falsePositive }).from(t.alerts).where(and(eq(t.alerts.status, 'closed'), sql`${t.alerts.module} in ('Transaction Monitoring', 'AML / KYC', 'AML / SAR')`)).all()
      return closed.length ? Math.round(100 * closed.filter(c => c.fp).length / closed.length) : null
    })(),
    customers: {
      total: customers.length,
      pendingKyc: customers.filter(c => c.kycStatus === 'pending').length,
      high: customers.filter(c => c.riskRating === 'high').length,
      eddOutstanding: customers.filter(c => c.eddStatus === 'required' || c.eddStatus === 'in_progress').length,
      pep: customers.filter(c => c.pep).length,
      sanctionsReview: customers.filter(c => c.sanctionsStatus === 'potential_match').length,
    },
    transactions24h: { count: tx24?.n ?? 0, value: tx24?.v ?? 0, pendingHighValue: pendingHv },
  }
})
