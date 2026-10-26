import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = riskBody.partial().extend({
  status: z.enum(['open', 'closed']).optional(),
  escalatedToBoard: z.boolean().optional(),
  reviewed: z.boolean().optional(),
})

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'risk:write')
  const id = parseId(event)
  const { controlIds, reviewed, ...patch } = await validateBody(event, body)
  const { risks } = tables
  const db = useDb()
  const before = db.select().from(risks).where(eq(risks.id, id)).get()
  if (!before) notFound('Risk')

  const updated = db.update(risks).set({ ...patch, ...(reviewed ? { lastReviewedAt: new Date() } : {}) }).where(eq(risks.id, id)).returning().get()
  if (controlIds) setLinks('risk', id, 'control', controlIds)
  if (patch.escalatedToBoard && !before.escalatedToBoard) {
    notifyRole('BOARD', { severity: 'high', title: `Material risk escalated: ${before.ref}`, body: before.title, link: `/risks?id=${id}` })
  }
  writeAudit(actorOf(user), 'risk.updated', 'risk', id, { ref: before.ref, fields: Object.keys(patch), controlsRemapped: !!controlIds, reviewed: !!reviewed }, event)
  return updated
})
