export default defineEventHandler(async (event) => {
  await requirePermission(event, 'programme:read')
  const db = useDb()
  const phases = db.select().from(tables.programmePhases).orderBy(tables.programmePhases.id).all()
  const tasks = db.select().from(tables.programmeTasks).all()
  return phases.map(p => {
    const own = tasks.filter(t => t.phaseId === p.id)
    return { ...p, tasks: own, progress: own.length ? Math.round(100 * own.filter(t => t.done).length / own.length) : 0 }
  })
})
