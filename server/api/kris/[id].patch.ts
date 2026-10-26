import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ value: z.number().optional(), amber: z.number().optional(), red: z.number().optional() })
  .refine(b => b.amber === undefined || b.red === undefined || b.amber < b.red, 'Amber threshold must be below red')

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'risk:write')
  const id = parseId(event)
  const patch = await validateBody(event, body)
  const { kris } = tables
  const db = useDb()
  const kri = db.select().from(kris).where(eq(kris.id, id)).get()
  if (!kri) notFound('KRI')
  const history = patch.value !== undefined ? [...kri.history.slice(-11), patch.value] : kri.history
  const updated = db.update(kris).set({ ...patch, history, updatedAt: new Date() }).where(eq(kris.id, id)).returning().get()
  writeAudit(actorOf(user), 'kri.updated', 'kri', id, { name: kri.name, from: kri.value, to: updated.value }, event)
  runCcmCycle() // re-evaluate thresholds immediately
  return updated
})
