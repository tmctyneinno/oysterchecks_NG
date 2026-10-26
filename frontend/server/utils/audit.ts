import type { H3Event } from 'h3'
import { desc } from 'drizzle-orm'

export interface Actor { id: number | null, name: string }
export const SYSTEM_ACTOR: Actor = { id: null, name: 'CCM Engine' }

const GENESIS = '0'.repeat(64)

/** Canonical form that is hashed, must stay stable or chain verification will fail. */
const canonical = (r: { at: Date, actorId: number | null, actorName: string, action: string, entityType: string, entityId: string | null, detail: unknown }) =>
  JSON.stringify([r.at.getTime(), r.actorId, r.actorName, r.action, r.entityType, r.entityId, r.detail ?? null])

/**
 * Appends to the hash-chained audit log (§2.3, ISO 27001 A.12.4). Each row stores the hash of the
 * previous row, so any tampering breaks verification from that point on.
 */
export function writeAudit(
  actor: Actor,
  action: string,
  entityType: string,
  entityId: string | number | null,
  detail?: Record<string, unknown>,
  event?: H3Event,
) {
  const db = useDb()
  const { auditLog } = tables
  const prev = db.select({ hash: auditLog.hash }).from(auditLog).orderBy(desc(auditLog.id)).limit(1).get()
  const row = {
    at: new Date(),
    actorId: actor.id,
    actorName: actor.name,
    action,
    entityType,
    entityId: entityId == null ? null : String(entityId),
    detail: detail ?? null,
  }
  const prevHash = prev?.hash ?? GENESIS
  const hash = sha256(prevHash + canonical(row))
  db.insert(auditLog).values({ ...row, detail: row.detail ?? undefined, ip: event ? getRequestIP(event, { xForwardedFor: true }) ?? null : null, prevHash, hash }).run()
  publish({ type: 'activity', payload: { action, entityType, entityId: row.entityId, actorName: actor.name, at: row.at.toISOString() } })
}

export const actorOf = (user: { id: number, name: string }): Actor => ({ id: user.id, name: user.name })

export function verifyAuditChain() {
  const rows = useDb().select().from(tables.auditLog).orderBy(tables.auditLog.id).all()
  let prev = GENESIS
  for (const r of rows) {
    const expected = sha256(prev + canonical({ ...r, detail: r.detail ?? null }))
    if (r.prevHash !== prev || r.hash !== expected) return { valid: false, brokenAt: r.id, checked: rows.length }
    prev = r.hash
  }
  return { valid: true, brokenAt: null, checked: rows.length }
}
