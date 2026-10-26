import { eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const row = useDb().select().from(tables.evidence).where(eq(tables.evidence.id, parseId(event))).get()
  if (!row) notFound('Evidence')
  const access = EVIDENCE_ACCESS[row.entityType]
  if (!access || !can(user.role, access.read)) throw createError({ statusCode: 403, statusMessage: 'Not permitted' })

  writeAudit(actorOf(user), 'evidence.downloaded', row.entityType, row.entityId, { evidenceId: row.id, filename: row.filename }, event)
  setResponseHeaders(event, {
    'content-type': row.mime,
    'content-disposition': `attachment; filename="${row.filename.replace(/"/g, '')}"`,
    'x-content-sha256': row.sha256,
  })
  return readEvidence(row.storageKey)
})
