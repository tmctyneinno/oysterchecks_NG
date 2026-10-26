import { and, eq, inArray, sql } from 'drizzle-orm'
import type { SQLiteColumn, SQLiteTable } from 'drizzle-orm/sqlite-core'

/** Next human-readable reference, e.g. ALT-000124. */
export function nextRef(table: SQLiteTable, refColumn: SQLiteColumn, prefix: string, pad = 4) {
  const row = useDb().select({ max: sql<string | null>`max(${refColumn})` }).from(table)
    .where(sql`${refColumn} like ${prefix + '-%'}`).get()
  const n = row?.max ? Number(row.max.slice(prefix.length + 1)) + 1 : 1
  return `${prefix}-${String(n).padStart(pad, '0')}`
}

export function userNames(ids: (number | null | undefined)[]) {
  const unique = [...new Set(ids.filter((x): x is number => typeof x === 'number'))]
  if (!unique.length) return new Map<number, string>()
  const rows = useDb().select({ id: tables.users.id, name: tables.users.name }).from(tables.users).where(inArray(tables.users.id, unique)).all()
  return new Map(rows.map(r => [r.id, r.name]))
}

export function linksFrom(fromType: string, fromId: number, toType?: string) {
  const { links } = tables
  return useDb().select().from(links)
    .where(and(eq(links.fromType, fromType), eq(links.fromId, fromId), toType ? eq(links.toType, toType) : undefined)).all()
}

/** Links are stored once and read in both directions. */
export function linkedIds(type: string, id: number, otherType: string) {
  const { links } = tables
  const db = useDb()
  const out = db.select({ id: links.toId }).from(links)
    .where(and(eq(links.fromType, type), eq(links.fromId, id), eq(links.toType, otherType))).all().map(r => r.id)
  const inbound = db.select({ id: links.fromId }).from(links)
    .where(and(eq(links.toType, type), eq(links.toId, id), eq(links.fromType, otherType))).all().map(r => r.id)
  return [...new Set([...out, ...inbound])]
}

export function setLinks(fromType: string, fromId: number, toType: string, toIds: number[]) {
  const { links } = tables
  const db = useDb()
  db.delete(links).where(and(eq(links.fromType, fromType), eq(links.fromId, fromId), eq(links.toType, toType))).run()
  if (toIds.length) db.insert(links).values(toIds.map(toId => ({ fromType, fromId, toType, toId }))).onConflictDoNothing().run()
}

// ── PII masking (§6.5: OysterChecks Admin sees customer/employee PII masked) ──

export const maskName = (name: string) => name.split(/\s+/).map(p => p[0] + '•'.repeat(Math.max(2, p.length - 1))).join(' ')

export function maskPii<T extends { name?: string, dob?: string | null }>(rows: T[], role: RoleKey): T[] {
  if (can(role, 'pii:view')) return rows
  return rows.map(r => ({ ...r, ...(r.name ? { name: maskName(r.name) } : {}), ...('dob' in r ? { dob: r.dob ? '••••-••-••' : null } : {}) }))
}
