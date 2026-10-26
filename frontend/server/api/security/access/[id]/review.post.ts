import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ decision: z.enum(['retain', 'revoke']), note: z.string().trim().min(5).max(1000) })

/** Access recertification (CCM-007). "Revoke" drops privileged status; deprovisioning happens in the IAM system. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'security:write')
  const id = parseId(event)
  const { decision, note } = await validateBody(event, body)
  const { accessAccounts } = tables
  const acct = useDb().select().from(accessAccounts).where(eq(accessAccounts.id, id)).get()
  if (!acct) notFound('Account')
  const updated = useDb().update(accessAccounts).set({
    lastReviewedAt: new Date(), reviewedById: user.id, ...(decision === 'revoke' ? { privileged: false, groups: [] } : {}),
  }).where(eq(accessAccounts.id, id)).returning().get()
  writeAudit(actorOf(user), `access.${decision}`, 'access_account', id, { account: acct.account, system: acct.system, note }, event)
  runCcmCycle()
  return updated
})
