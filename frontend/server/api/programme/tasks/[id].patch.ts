import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ done: z.boolean() })

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'programme:write')
  const id = parseId(event)
  const { done } = await validateBody(event, body)
  const db = useDb()
  const { programmeTasks, programmePhases } = tables
  const existing = db.select().from(programmeTasks).where(eq(programmeTasks.id, id)).get()
  if (!existing) notFound('Task')
  const phase = db.select().from(programmePhases).where(eq(programmePhases.id, existing.phaseId)).get()!
  if (phase.status === 'complete') throw createError({ statusCode: 409, statusMessage: 'Phase already signed off' })

  const task = db.update(programmeTasks).set({ done, doneAt: done ? new Date() : null }).where(eq(programmeTasks.id, id)).returning().get()
  if (done && phase.status === 'not_started') db.update(programmePhases).set({ status: 'in_progress' }).where(eq(programmePhases.id, phase.id)).run()
  writeAudit(actorOf(user), 'programme.task_updated', 'programme_task', id, { title: task.title, done }, event)
  return task
})
