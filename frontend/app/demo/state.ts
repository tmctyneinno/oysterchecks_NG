// Demo state that sits on top of the fixtures.
//  - Session: per browser tab (sessionStorage), so two tabs can be signed in as different people.
//  - Overlay: every change made during the demo (patches, new records, timelines, audit entries,
//    notifications). Shared by all tabs through localStorage, so a sign-off in one tab shows up
//    in the other, which is how the four-eyes and live-update parts of the demo work.

import type { DemoUser } from './fixtures'

const SESSION_KEY = 'oc-demo-session'
const OVERLAY_KEY = 'oc-demo-overlay-v1'

export interface Overlay {
  seq: number
  /** resource → id → changed fields */
  patches: Record<string, Record<string, Record<string, any>>>
  /** resource → records created during the demo, newest first */
  created: Record<string, any[]>
  /** `${resource}:${id}` → appended timeline entries (alert events, policy history) */
  events: Record<string, any[]>
  /** `${entityType}:${id}` → uploaded evidence metadata */
  evidence: Record<string, any[]>
  /** New audit log rows, newest first */
  audit: any[]
  notifications: any[]
  /** userId → notification ids marked read */
  read: Record<string, number[]>
  /** Live event types raised by the last change, so other tabs know what to refresh */
  last: string[]
}

const empty = (): Overlay => ({ seq: 100_000, patches: {}, created: {}, events: {}, evidence: {}, audit: [], notifications: [], read: {}, last: [] })

function readJson<T>(storage: Storage | undefined, key: string): T | null {
  try {
    const raw = storage?.getItem(key)
    return raw ? JSON.parse(raw) as T : null
  }
  catch { return null }
}

function writeJson(storage: Storage | undefined, key: string, value: unknown) {
  try {
    if (value === null) storage?.removeItem(key)
    else storage?.setItem(key, JSON.stringify(value))
  }
  catch { /* storage full or blocked: the demo keeps working in memory */ }
}

const local = () => (typeof localStorage === 'undefined' ? undefined : localStorage)
const session = () => (typeof sessionStorage === 'undefined' ? undefined : sessionStorage)

export const readSession = () => readJson<DemoUser>(session(), SESSION_KEY)
export const writeSession = (user: DemoUser | null) => writeJson(session(), SESSION_KEY, user)

export let overlay: Overlay = { ...empty(), ...readJson<Overlay>(local(), OVERLAY_KEY) }

export function saveOverlay(types: string[]) {
  overlay.last = types
  writeJson(local(), OVERLAY_KEY, overlay)
}

export function resetOverlay() {
  overlay = empty()
  writeJson(local(), OVERLAY_KEY, null)
}

/** Picks up changes saved by another tab. Returns the live event types to replay locally. */
export function reloadOverlay(): string[] {
  overlay = { ...empty(), ...readJson<Overlay>(local(), OVERLAY_KEY) }
  return overlay.last.length ? overlay.last : ['data']
}

export const OVERLAY_STORAGE_KEY = OVERLAY_KEY

export const nextId = () => ++overlay.seq

export function patch(resource: string, id: number | string, fields: Record<string, any>) {
  const byId = (overlay.patches[resource] ??= {})
  byId[String(id)] = { ...byId[String(id)], ...fields }
}

/** Applies stored changes to a fixture record (in place) and returns it. */
export function applyPatch<T extends { id: number | string }>(resource: string, row: T): T {
  const p = overlay.patches[resource]?.[String(row.id)]
  return p ? Object.assign(row, p) : row
}

export function create<T>(resource: string, row: T): T {
  ;(overlay.created[resource] ??= []).unshift(row)
  return row
}

export const createdOf = <T = any>(resource: string): T[] => structuredClone(overlay.created[resource] ?? [])

export function addEvent(key: string, row: any) {
  ;(overlay.events[key] ??= []).push(row)
}

export const eventsOf = (key: string): any[] => structuredClone(overlay.events[key] ?? [])
