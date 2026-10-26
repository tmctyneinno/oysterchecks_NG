import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  result: z.enum(['effective', 'partially_effective', 'ineffective']),
  comment: z.string().trim().max(2000).optional(),
}).refine(b => b.result === 'effective' || (b.comment && b.comment.length >= 10), { message: 'Explain why the control is not fully effective', path: ['comment'] })

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'csa:submit')
  const id = parseId(event)
  const input = await validateBody(event, body)
  const { csaAssessments: csa } = tables
  const db = useDb()
  const row = db.select().from(csa).where(whereAll(eq(csa.id, id), buScope(user, csa.businessUnit))).get()
  if (!row) notFound('Assessment')
  if (row.submittedAt) throw createError({ statusCode: 409, statusMessage: 'Already submitted' })
  const updated = db.update(csa).set({ ...input, submittedAt: new Date(), submittedById: user.id }).where(eq(csa.id, id)).returning().get()
  writeAudit(actorOf(user), 'csa.submitted', 'csa', id, { result: input.result, period: row.period }, event)
  runCcmCycle()
  return updated
})
