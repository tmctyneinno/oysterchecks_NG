export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const q = getQuery(event)
  const entityType = String(q.entityType ?? '')
  const entityId = Number(q.entityId)
  const access = EVIDENCE_ACCESS[entityType]
  if (!access || !Number.isInteger(entityId)) throw createError({ statusCode: 400, statusMessage: 'Unknown evidence target' })
  if (!can(user.role, access.read)) throw createError({ statusCode: 403, statusMessage: 'Not permitted' })
  return listEvidence(entityType, entityId)
})
