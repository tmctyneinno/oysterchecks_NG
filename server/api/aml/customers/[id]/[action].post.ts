import { eq } from 'drizzle-orm'
import { z } from 'zod'

// Customer due-diligence actions (§4.5 steps 1–4, 8).
const schemas = {
  'verify-kyc': z.object({ note: z.string().trim().min(5).max(1000) }),
  'refresh-kyc': z.object({ note: z.string().trim().min(5).max(1000) }),
  'start-edd': z.object({}),
  'complete-edd': z.object({ sourceOfFunds: z.string().trim().min(10).max(2000), uboVerified: z.literal(true), seniorApproverId: z.number().int() }),
  rescreen: z.object({ result: z.enum(['clear', 'potential_match', 'confirmed_match']) }),
}
type Action = keyof typeof schemas

export default defineEventHandler(async (event) => {
  const action = getRouterParam(event, 'action') as Action
  if (!(action in schemas)) throw createError({ statusCode: 404, statusMessage: 'Unknown action' })
  const user = await requirePermission(event, 'aml:write')
  const id = parseId(event)
  const input = await validateBody(event, schemas[action]) as Record<string, any>
  const db = useDb()
  const t = tables
  const c = db.select().from(t.customers).where(eq(t.customers.id, id)).get()
  if (!c) notFound('Customer')

  const now = new Date()
  let patch: Partial<typeof c> = {}
  switch (action) {
    case 'verify-kyc':
      if (c.sanctionsStatus === 'pending') throw createError({ statusCode: 409, statusMessage: 'Sanctions screening must complete before KYC verification' })
      patch = { kycStatus: 'verified', kycVerifiedAt: now }
      break
    case 'refresh-kyc':
      patch = { kycVerifiedAt: now }
      break
    case 'start-edd':
      patch = { eddStatus: 'in_progress' }
      break
    case 'complete-edd': {
      // §4.5 step 4: senior management approval required, must be a different person.
      if (input.seniorApproverId === user.id) throw createError({ statusCode: 403, statusMessage: 'Senior approval must come from someone other than the preparer' })
      const approver = db.select().from(t.users).where(eq(t.users.id, input.seniorApproverId)).get()
      if (!approver || !['MLRO', 'CCO', 'CRO'].includes(approver.role)) throw createError({ statusCode: 400, statusMessage: 'Approver must be MLRO, CCO or CRO' })
      patch = { eddStatus: 'complete', eddCompletedAt: now }
      notifyUser(approver.id, { severity: 'info', title: `EDD approval recorded for ${c.ref}`, link: '/aml' })
      break
    }
    case 'rescreen': {
      const score = scoreAml({ ...c, sanctionsStatus: input.result })
      patch = { sanctionsStatus: input.result, riskRating: input.result === 'clear' ? c.riskRating : ratingForScore(score.score) }
      if (patch.riskRating === 'high' && c.eddStatus === 'not_required') patch.eddStatus = 'required'
      if (input.result !== 'clear') {
        db.insert(t.amlCases).values({
          ref: nextRef(t.amlCases, t.amlCases.ref, 'AML', 4), customerId: id, type: 'sanctions', score: score.score, scoreFactors: score.factors,
          status: input.result === 'confirmed_match' ? 'escalated' : 'new', openedAt: now,
        }).run()
        if (input.result === 'confirmed_match') notifyRole('MLRO', { severity: 'critical', title: `Confirmed sanctions match: ${c.ref}`, link: '/aml' })
      }
      break
    }
  }
  const updated = db.update(t.customers).set(patch).where(eq(t.customers.id, id)).returning().get()
  writeAudit(actorOf(user), `customer.${action.replace('-', '_')}`, 'customer', id, { ref: c.ref, ...(action === 'rescreen' ? { result: input.result } : {}) }, event)
  runCcmCycle()
  return maskPii([updated], user.role)[0]
})
