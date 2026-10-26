import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  vettingStatus: z.enum(['not_started', 'in_progress', 'cleared', 'failed']).optional(),
  startDate: z.coerce.date().optional(),
  terminated: z.boolean().optional(),
})

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'hr:write')
  const id = parseId(event)
  const input = await validateBody(event, body)
  const { employees } = tables
  const before = useDb().select().from(employees).where(eq(employees.id, id)).get()
  if (!before) notFound('Employee')
  const updated = useDb().update(employees).set({
    ...(input.vettingStatus ? { vettingStatus: input.vettingStatus, vettingClearedAt: input.vettingStatus === 'cleared' ? new Date() : null } : {}),
    ...(input.startDate ? { startDate: input.startDate } : {}),
    ...(input.terminated ? { terminatedAt: new Date() } : {}),
  }).where(eq(employees.id, id)).returning().get()
  if (input.vettingStatus === 'failed') notifyRole('CISO', { severity: 'high', title: `Vetting failed: ${before.ref}`, body: 'Ensure no system access is provisioned.', link: '/vetting' })
  writeAudit(actorOf(user), 'employee.vetting_updated', 'employee', id, { ref: before.ref, from: before.vettingStatus, to: updated.vettingStatus, terminated: !!input.terminated }, event)
  runCcmCycle()
  return maskPii([updated], user.role)[0]
})
