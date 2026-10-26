import { mkdirSync, readFileSync, writeFileSync, existsSync } from 'node:fs'
import { join, resolve } from 'node:path'
import { and, desc, eq } from 'drizzle-orm'

// Evidence store (§1.2 "audit-ready evidence packs"). Files are content-addressed by SHA-256, so the
// hash recorded at upload proves the file has not been altered since.

/** Which permissions are needed to read / attach evidence for each entity type. */
export const EVIDENCE_ACCESS: Record<string, { read: Permission, write: Permission }> = {
  alert: { read: 'alerts:read', write: 'alerts:act' },
  incident: { read: 'incidents:read', write: 'incidents:write' },
  audit_finding: { read: 'audit:read', write: 'audit:write' },
  policy: { read: 'governance:read', write: 'governance:write' },
  csa: { read: 'csa:read', write: 'csa:submit' },
  control: { read: 'controls:read', write: 'controls:write' },
  supplier: { read: 'tprm:read', write: 'tprm:write' },
  aml_case: { read: 'aml:read', write: 'aml:write' },
  obligation: { read: 'compliance:read', write: 'compliance:write' },
  regulatory_report: { read: 'compliance:read', write: 'regreport:submit' },
  esg_metric: { read: 'esg:read', write: 'esg:write' },
}

export const ALLOWED_MIME = new Set([
  'application/pdf', 'image/png', 'image/jpeg', 'text/plain', 'text/csv',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'message/rfc822',
])
export const MAX_EVIDENCE_BYTES = 15 * 1024 * 1024

const dir = () => {
  const d = resolve(useRuntimeConfig().evidenceDir)
  mkdirSync(d, { recursive: true })
  return d
}

export function storeEvidence(data: Buffer) {
  const hash = sha256(data)
  const path = join(dir(), hash)
  if (!existsSync(path)) writeFileSync(path, data)
  return hash
}

export const readEvidence = (storageKey: string) => readFileSync(join(dir(), storageKey))

export function listEvidence(entityType: string, entityId: number) {
  const { evidence } = tables
  const rows = useDb().select().from(evidence).where(and(eq(evidence.entityType, entityType), eq(evidence.entityId, entityId))).orderBy(desc(evidence.uploadedAt)).all()
  const names = userNames(rows.map(r => r.uploadedById))
  return rows.map(({ storageKey: _key, ...r }) => ({ ...r, uploadedByName: names.get(r.uploadedById) ?? 'Unknown' }))
}
