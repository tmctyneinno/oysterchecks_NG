import { and, desc, like, lt, or } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  await requirePermission(event, 'auditlog:read')
  const q = getQuery(event)
  const { auditLog } = tables
  const before = q.before ? Number(q.before) : undefined
  const term = q.q ? `%${String(q.q)}%` : undefined
  const rows = useDb().select().from(auditLog)
    .where(and(before ? lt(auditLog.id, before) : undefined, term ? or(like(auditLog.action, term), like(auditLog.actorName, term), like(auditLog.entityType, term), like(auditLog.entityId, term)) : undefined))
    .orderBy(desc(auditLog.id)).limit(100).all()
  return { rows, nextBefore: rows.length === 100 ? rows.at(-1)!.id : null }
})
