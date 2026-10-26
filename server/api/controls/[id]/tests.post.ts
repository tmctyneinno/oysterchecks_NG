import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ result: z.enum(['pass', 'fail']), notes: z.string().trim().min(3).max(2000) })

/** Manual test result (2nd-line independent testing, §4.6 step 5). */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'controls:write')
  const id = parseId(event)
  const input = await validateBody(event, body)
  const control = useDb().select().from(tables.controls).where(eq(tables.controls.id, id)).get()
  if (!control) notFound('Control')
  useDb().insert(tables.controlTests).values({ controlId: id, at: new Date(), method: 'manual', testerId: user.id, ...input }).run()
  const effectiveness = recomputeEffectiveness(id)
  writeAudit(actorOf(user), 'control.tested', 'control', id, { ref: control.ref, result: input.result, effectiveness }, event)
  runCcmCycle()
  return { effectiveness }
})
