export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const parts = await readMultipartFormData(event)
  if (!parts) throw createError({ statusCode: 400, statusMessage: 'Expected multipart form data' })

  const field = (name: string) => parts.find(p => p.name === name && !p.filename)?.data.toString('utf8')
  const entityType = field('entityType') ?? ''
  const entityId = Number(field('entityId'))
  const file = parts.find(p => p.name === 'file' && p.filename)

  const access = EVIDENCE_ACCESS[entityType]
  if (!access || !Number.isInteger(entityId)) throw createError({ statusCode: 400, statusMessage: 'Unknown evidence target' })
  if (!can(user.role, access.write)) throw createError({ statusCode: 403, statusMessage: 'Your role cannot attach evidence here' })
  if (!file?.data.length) throw createError({ statusCode: 400, statusMessage: 'No file uploaded' })
  if (file.data.length > MAX_EVIDENCE_BYTES) throw createError({ statusCode: 413, statusMessage: 'File exceeds 15 MB limit' })
  const mime = file.type ?? 'application/octet-stream'
  if (!ALLOWED_MIME.has(mime)) throw createError({ statusCode: 415, statusMessage: `File type ${mime} is not accepted as evidence` })

  const storageKey = storeEvidence(file.data)
  const row = useDb().insert(tables.evidence).values({
    entityType, entityId, filename: file.filename!.slice(0, 200), mime, size: file.data.length, sha256: storageKey, storageKey,
    uploadedById: user.id, uploadedAt: new Date(),
  }).returning().get()

  if (entityType === 'alert') addAlertEvent(entityId, actorOf(user), 'evidence', `Evidence uploaded: ${row.filename}`)
  writeAudit(actorOf(user), 'evidence.uploaded', entityType, entityId, { filename: row.filename, sha256: row.sha256 }, event)
  const { storageKey: _k, ...safe } = row
  return safe
})
