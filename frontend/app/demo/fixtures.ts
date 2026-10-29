// Hardcoded demo data. fixtures.json holds every API response the UI reads, captured once per demo
// role (so role restrictions, BU scoping and PII masking match the real platform). Bodies are shared
// between roles and deduplicated by content.

import type { RoleKey } from '#shared/utils/rbac'

export interface DemoUser { id: number, name: string, email: string, role: RoleKey, businessUnit: string | null }
type Entry = string | { status: number, message: string }
interface FixtureFile {
  capturedAt: string
  users: Record<string, DemoUser>
  roles: Record<string, Record<string, Entry>>
  bodies: Record<string, unknown>
}

const DAY = 86_400_000
let data: FixtureFile | null = null

/**
 * Loads the fixtures and moves every date forward by the time elapsed since capture, so "overdue",
 * "due in 3 days" and SLA countdowns read the same whenever the demo is opened. Birth dates
 * (pre-2020) are left alone.
 */
export async function loadFixtures() {
  if (data) return data
  const { default: raw } = await import('./fixtures.json?raw')
  const capturedAt = Date.parse(/"capturedAt":"([^"]+)"/.exec(raw)![1]!)
  const delta = Date.now() - capturedAt
  const days = Math.round(delta / DAY)
  const shifted = raw
    .replace(/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,3})?Z/g, s => new Date(Date.parse(s) + delta).toISOString())
    .replace(/\b20[2-4]\d-\d{2}-\d{2}(?!T)/g, s => new Date(Date.parse(s) + days * DAY).toISOString().slice(0, 10))
  data = JSON.parse(shifted) as FixtureFile
  return data
}

function fx() {
  if (!data) throw new Error('Demo fixtures not loaded')
  return data
}

export const demoUsers = () => Object.values(fx().users)
export const userByEmail = (email: string) => fx().users[email.toLowerCase()] ?? null

export class DemoHttpError extends Error {
  statusCode: number
  statusMessage: string
  data: { statusCode: number, statusMessage: string }
  constructor(statusCode: number, statusMessage: string) {
    super(statusMessage)
    this.statusCode = statusCode
    this.statusMessage = statusMessage
    this.data = { statusCode, statusMessage }
  }
}

export function fail(statusCode: number, statusMessage: string): never {
  throw new DemoHttpError(statusCode, statusMessage)
}

/** The captured response for this user and URL: a fresh copy, `undefined` if never captured. Captured errors are rethrown. */
export function captured<T = any>(email: string, url: string): T | undefined {
  const entry = fx().roles[email]?.[url]
  if (entry === undefined) return undefined
  if (typeof entry !== 'string') fail(entry.status, entry.message)
  return structuredClone(fx().bodies[entry]) as T
}

/** Same as `captured`, but from whichever role saw the record first (CRO first, so PII is unmasked). */
export function capturedAny<T = any>(url: string): T | undefined {
  for (const map of Object.values(fx().roles)) {
    const entry = map[url]
    if (typeof entry === 'string') return structuredClone(fx().bodies[entry]) as T
  }
  return undefined
}

/** Every distinct record of a list endpoint across all roles, keyed by id. */
export function indexOf<T extends { id: number | string }>(urls: string[], pick: (body: any) => T[] = b => b): Map<T['id'], T> {
  const out = new Map<T['id'], T>()
  for (const map of Object.values(fx().roles)) {
    for (const url of urls) {
      const entry = map[url]
      if (typeof entry !== 'string') continue
      for (const row of pick(fx().bodies[entry]) ?? []) if (!out.has(row.id)) out.set(row.id, structuredClone(row))
    }
  }
  return out
}
