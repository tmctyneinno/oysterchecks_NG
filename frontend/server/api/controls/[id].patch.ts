import { eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'controls:write')
  const id = parseId(event)
  const { riskIds: _ignored, ...patch } = await validateBody(event, controlBody.partial())
  const { controls } = tables
  const updated = useDb().update(controls).set(patch).where(eq(controls.id, id)).returning().get()
  if (!updated) notFound('Control')
  writeAudit(actorOf(user), 'control.updated', 'control', id, { ref: updated.ref, fields: Object.keys(patch) }, event)
  return updated
})
