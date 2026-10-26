import { and, eq } from 'drizzle-orm'

/** Quality gate: a phase is signed off only when all its tasks are done and the previous phase is complete (§7). */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'programme:write')
  const id = parseId(event)
  const db = useDb()
  const { programmePhases, programmeTasks } = tables
  const phase = db.select().from(programmePhases).where(eq(programmePhases.id, id)).get()
  if (!phase) notFound('Phase')
  if (phase.status === 'complete') throw createError({ statusCode: 409, statusMessage: 'Already signed off' })
  const prev = db.select().from(programmePhases).where(eq(programmePhases.id, id - 1)).get()
  if (prev && prev.status !== 'complete') throw createError({ statusCode: 409, statusMessage: `Phase ${prev.id} (${prev.name}) must be signed off first` })
  const open = db.select().from(programmeTasks).where(and(eq(programmeTasks.phaseId, id), eq(programmeTasks.done, false))).all()
  if (open.length) throw createError({ statusCode: 409, statusMessage: `${open.length} task(s) outstanding` })
  const updated = db.update(programmePhases).set({ status: 'complete', signedOffAt: new Date() }).where(eq(programmePhases.id, id)).returning().get()
  writeAudit(actorOf(user), 'programme.phase_signed_off', 'programme_phase', id, { name: phase.name, gate: phase.gate }, event)
  return updated
})
