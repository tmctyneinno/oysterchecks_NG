import { desc } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'aml:read')
  const rows = useDb().select().from(tables.customers).orderBy(desc(tables.customers.createdAt)).all()
  const now = Date.now()
  return maskPii(rows, user.role).map((c) => {
    let kycExpiresAt: Date | null = null
    if (c.kycVerifiedAt) {
      kycExpiresAt = new Date(c.kycVerifiedAt)
      kycExpiresAt.setMonth(kycExpiresAt.getMonth() + KYC_REFRESH_MONTHS[c.riskRating])
    }
    return { ...c, kycExpiresAt, kycExpired: !!kycExpiresAt && kycExpiresAt.getTime() < now }
  })
})
