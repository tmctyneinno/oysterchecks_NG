// In-browser stand-in for the platform API. Pages keep calling `/api/...` exactly as they would
// against a server; this module answers from the hardcoded fixtures plus the demo overlay, and
// applies the same business rules (RBAC, four-eyes, MLRO-only SAR submission, closure gates).

import { ROLES, can, type Permission, type RoleKey } from '#shared/utils/rbac'
import { ACK_MINUTES, ESCALATION_TARGETS, computeDueAt, escalationPathLabel, slaLabel, type EscalationTarget, type Severity, type SlaSpec } from '#shared/utils/sla'
import { scoreRisk, type ControlEffectiveness } from '#shared/utils/risk'
import { countryRiskOf, ratingForScore, scoreAml } from '#shared/utils/aml'
import { SUPPLIER_REVIEW_MONTHS, classifySupplier } from '#shared/utils/tprm'
import { NOTIFICATION_RULES } from '#shared/utils/incidents'
import { type DemoUser, captured, capturedAny, demoUsers, fail, indexOf, userByEmail } from './fixtures'
import { addEvent, applyPatch, create, createdOf, eventsOf, nextId, overlay, patch, readSession, saveOverlay, writeSession } from './state'

export const DEMO_PASSWORD = 'OysterDemo!2026'

// Demo records are plain JSON from the fixtures; the typed contract lives in app/types/demo-api.d.ts.
type Row = any
interface Ctx { user: DemoUser | null, query: Record<string, string>, body: any, params: string[] }
type Handler = (ctx: Ctx) => unknown

const routes: { method: string, re: RegExp, handler: Handler }[] = []
function route(method: string, pattern: string, handler: Handler) {
  routes.push({ method, re: new RegExp(`^${pattern.replace(/:\w+/g, '([^/]+)')}$`), handler })
}

/** Live event types raised by each mutation, replayed in this tab and in other open tabs. */
let changed = new Set<string>()
let onChange: (types: string[]) => void = () => {}
export const setChangeListener = (fn: (types: string[]) => void) => { onChange = fn }

export async function handle(method: string, path: string, query: Record<string, string>, body: unknown) {
  const match = routes.find(r => r.method === method && r.re.test(path))
  if (!match) fail(404, `Not available in the demo: ${method} ${path}`)
  const params = match.re.exec(path)!.slice(1).map(decodeURIComponent)
  changed = new Set()
  const result = await match.handler({ user: readSession(), query, body: body ?? {}, params })
  if (method !== 'GET') {
    const types = changed.size ? [...changed] : ['data']
    saveOverlay(types)
    onChange(types)
  }
  return result === undefined ? null : structuredClone(result)
}

// ── Helpers ──────────────────────────────────────────────────────────────────────────────────

const nowIso = () => new Date().toISOString()
const DAY = 86_400_000
const OPEN_STATUSES = ['open', 'acknowledged', 'investigating', 'escalated', 'pending_closure']

function requireUser(ctx: Ctx): DemoUser {
  if (!ctx.user) fail(401, 'Authentication required')
  return ctx.user
}

function requirePerm(ctx: Ctx, ...permissions: Permission[]): DemoUser {
  const user = requireUser(ctx)
  const missing = permissions.filter(p => !can(user.role, p))
  if (missing.length) fail(403, `Your role does not permit: ${missing.join(', ')}`)
  return user
}

/** BU-scoped roles only see records from their own business unit. */
const visible = (user: DemoUser, row: Row) =>
  ROLES[user.role].scope !== 'bu' || !('businessUnit' in row) || row.businessUnit === user.businessUnit

/** Fixture reads are captured per role; a missing capture means the role cannot see that record. */
function read<T = any>(user: DemoUser, url: string, what = 'Record'): T {
  const body = captured<T>(user.email, url)
  if (body === undefined) fail(404, `${what} not found`)
  return body
}

function required(input: Row, ...fields: string[]) {
  for (const f of fields) {
    const v = input[f]
    if (v === undefined || v === null || (typeof v === 'string' && !v.trim())) fail(400, `${labelOf(f)} is required`)
  }
}

function minLength(value: unknown, min: number, message: string) {
  if (typeof value !== 'string' || value.trim().length < min) fail(400, message)
  return value.trim()
}

const labelOf = (f: string) => f.replace(/([A-Z])/g, ' $1').replace(/^./, c => c.toUpperCase())
const toIso = (v: unknown) => (v ? new Date(v as string).toISOString() : null)
const addMonths = (iso: string, months: number) => { const d = new Date(iso); d.setMonth(d.getMonth() + months); return d.toISOString() }

// Every distinct record per resource across all roles' captures, keyed by id.
const SOURCES: Record<string, [string[], ((b: any) => Row[])?]> = {
  alerts: [['/api/alerts?view=all', '/api/alerts?view=closed']],
  rules: [['/api/ccm/rules'], b => b.rules],
  risks: [['/api/risks']],
  kris: [['/api/risks'], b => b.flatMap((r: Row) => r.kris)],
  controls: [['/api/controls']],
  obligations: [['/api/obligations']],
  reports: [['/api/regulatory-reports']],
  policies: [['/api/policies']],
  activities: [['/api/assurance'], b => b.activities],
  engagements: [['/api/audit'], b => b.engagements],
  findings: [['/api/audit'], b => b.findings],
  csa: [['/api/csa']],
  cases: [['/api/aml/cases']],
  transactions: [['/api/aml/transactions?pending=1']],
  customers: [['/api/aml/customers']],
  suppliers: [['/api/suppliers']],
  incidents: [['/api/incidents']],
  actions: [['/api/actions']],
  esg: [['/api/esg']],
  vetting: [['/api/vetting']],
  access: [['/api/security/access']],
  phases: [['/api/programme']],
  tasks: [['/api/programme'], b => b.flatMap((p: Row) => p.tasks)],
  integrations: [['/api/integrations'], b => b.integrations],
  users: [['/api/admin/users']],
  directory: [['/api/users/lookup']],
}
const indexes = new Map<string, Map<any, Row>>()
function idx(resource: string) {
  if (!indexes.has(resource)) {
    const [urls, pick] = SOURCES[resource]!
    indexes.set(resource, indexOf(urls, pick as any) as Map<any, Row>)
  }
  return indexes.get(resource)!
}

/** The record as it stands now (fixture or created, plus demo changes), or null. */
function current(resource: string, id: number | string): Row | null {
  const key = String(id)
  const made = overlay.created[resource]?.find(r => String(r.id) === key)
  const base = made ?? idx(resource).get(Number.isNaN(Number(id)) ? id : Number(id)) ?? idx(resource).get(key)
  return base ? applyPatch(resource, structuredClone(base)) : null
}

function mustFind(resource: string, id: number | string, what: string): Row {
  const row = current(resource, id)
  if (!row) fail(404, `${what} not found`)
  return row
}

/** Fixture list + records created in the demo (newest first), with demo changes applied. */
function listView(resource: string, base: Row[], user: DemoUser): Row[] {
  const ids = new Set(base.map(r => String(r.id)))
  const made = createdOf(resource).filter(r => !ids.has(String(r.id)) && visible(user, r))
  return [...made, ...base].map(r => applyPatch(resource, r))
}

const allUsers = () => [...demoUsers(), ...createdOf<DemoUser>('users')]
function userName(id: number | null | undefined) {
  if (!id) return null
  return allUsers().find(u => u.id === id)?.name ?? idx('directory').get(id)?.name ?? null
}
const usersWithRole = (role: RoleKey) => allUsers().filter(u => u.role === role)

function nextRef(resource: string, fallback: string): string {
  const refs = [...idx(resource).values(), ...createdOf(resource)].map(r => r.ref as string).filter(Boolean)
  const sample = refs[0] ?? fallback
  const [, prefix = fallback.replace(/\d+$/, ''), digits = '0'] = /^(.*?)(\d+)$/.exec(sample) ?? []
  const max = Math.max(0, ...refs.filter(r => r.startsWith(prefix)).map(r => Number(r.slice(prefix.length)) || 0))
  return `${prefix}${String(max + 1).padStart(digits.length, '0')}`
}

// ── Audit trail, notifications, evidence ────────────────────────────────────────────────────

async function sha256(data: ArrayBuffer | string): Promise<string> {
  const bytes = typeof data === 'string' ? new TextEncoder().encode(data) : new Uint8Array(data)
  if (globalThis.crypto?.subtle) {
    const digest = await crypto.subtle.digest('SHA-256', bytes)
    return [...new Uint8Array(digest)].map(b => b.toString(16).padStart(2, '0')).join('')
  }
  // Plain-HTTP hosting has no WebCrypto; a deterministic stand-in keeps the chain readable.
  let out = ''
  for (let seed = 0; out.length < 64; seed++) {
    let h = 0x811C9DC5 ^ seed
    for (const b of bytes) h = Math.imul(h ^ b, 0x01000193)
    out += (h >>> 0).toString(16).padStart(8, '0')
  }
  return out.slice(0, 64)
}

let fixtureAudit: Row[] | null = null
const fixtureAuditLog = () => (fixtureAudit ??= capturedAny<Row[]>('/api/admin/audit-log') ?? [])

async function audit(user: DemoUser | null, action: string, entityType: string | null, entityId: number | string | null, detail: Row | null = null) {
  const prev = overlay.audit[0] ?? fixtureAuditLog()[0]
  const row: Row = {
    id: (prev?.id ?? 0) + 1, at: nowIso(), actorId: user?.id ?? null, actorName: user?.name ?? 'System', action, entityType,
    entityId: entityId === null ? null : String(entityId), detail, ip: '10.40.2.18', prevHash: prev?.hash ?? '0'.repeat(64),
  }
  row.hash = await sha256(row.prevHash + JSON.stringify({ ...row, hash: undefined }))
  overlay.audit.unshift(row)
  changed.add('activity')
}

function notifyUser(userId: number | null | undefined, n: { severity: string, title: string, body?: string | null, link?: string | null }) {
  if (!userId) return
  overlay.notifications.unshift({ id: nextId(), userId, at: nowIso(), body: null, link: null, readAt: null, ...n })
  changed.add('notification')
}
const notifyRole = (role: RoleKey, n: Parameters<typeof notifyUser>[1]) => usersWithRole(role).forEach(u => notifyUser(u.id, n))

/** Uploaded files stay in this tab's memory; other tabs get a placeholder on download. */
export const evidenceBlobs = new Map<number, Blob>()
const evidenceFor = (entityType: string, entityId: number | string) => structuredClone(overlay.evidence[`${entityType}:${entityId}`] ?? [])
const evidenceCount = (entityType: string, entityId: number) => overlay.evidence[`${entityType}:${entityId}`]?.length ?? 0

// ── Sign-in ─────────────────────────────────────────────────────────────────────────────────

route('POST', '/api/auth/login', async ({ body }) => {
  const email = String(body.email ?? '').trim().toLowerCase()
  const user = userByEmail(email) ?? createdOf<DemoUser & { password?: string, active?: boolean }>('users').find(u => u.email === email)
  const patched = user ? current('users', user.id) : null
  const password = (user as Row | null)?.password ?? DEMO_PASSWORD
  if (!user || body.password !== password) fail(401, 'Invalid email or password')
  if (patched && patched.active === false) fail(403, 'Account disabled')
  const role = (patched?.role ?? user.role) as RoleKey
  const session: DemoUser = { id: user.id, name: user.name, email: user.email, role, businessUnit: patched?.businessUnit ?? user.businessUnit ?? null }
  writeSession(session)
  await audit(session, 'auth.login', 'user', user.id, { role })
  return { next: ROLES[role].landing }
})

route('POST', '/api/auth/logout', () => {
  writeSession(null)
  return { ok: true }
})

// ── Dashboard and reports ───────────────────────────────────────────────────────────────────

function allAlerts(): Row[] {
  const ids = new Set<number>()
  return [...createdOf('alerts'), ...idx('alerts').values()]
    .filter(a => !ids.has(a.id) && ids.add(a.id))
    .map(a => enrichAlert(applyPatch('alerts', structuredClone(a))))
}

route('GET', '/api/dashboard', (ctx) => {
  const user = requirePerm(ctx, 'dashboard:exec')
  const d = read(user, '/api/dashboard')
  const open = allAlerts().filter(a => OPEN_STATUSES.includes(a.status))
  d.generatedAt = nowIso()
  d.alerts = {
    open: open.length,
    overdue: open.filter(a => a.overdue).length,
    pendingClosure: open.filter(a => a.status === 'pending_closure').length,
    bySeverity: Object.fromEntries(['critical', 'high', 'medium', 'low'].map(s => [s, open.filter(a => a.severity === s).length])),
  }
  const cases = [...createdOf('cases'), ...idx('cases').values()].map(c => applyPatch('cases', c))
  d.aml = { open: cases.filter(c => c.status !== 'closed').length, sarPending: cases.filter(c => c.status === 'sar_decided').length }
  // Risk and control panels follow test results and register changes made during the demo.
  const register = listView('risks', capturedAny<Row[]>('/api/risks') ?? [], user).filter(r => r.status === 'open').map(riskView)
  d.risks.total = register.length
  d.risks.residualBands = Object.fromEntries(['critical', 'high', 'moderate', 'low'].map(b => [b, register.filter(r => r.residual.band === b).length]))
  d.risks.controlGaps = register.filter(r => r.controlGap).length
  d.risks.top = [...register].sort((a, b) => b.residual.score - a.residual.score || b.inherent.score - a.inherent.score).slice(0, 5)
    .map(r => ({ id: r.id, ref: r.ref, title: r.title, inherent: r.inherent.score, residual: r.residual.score, band: r.residual.band, ownerName: r.ownerName }))
  const controls = [...createdOf('controls'), ...idx('controls').values()].map(c => applyPatch('controls', structuredClone(c)))
  d.controls = {
    total: controls.length,
    effective: controls.filter(c => c.effectiveness === 'effective').length,
    partially: controls.filter(c => c.effectiveness === 'partially_effective').length,
    ineffective: controls.filter(c => c.effectiveness === 'ineffective').length,
    notTested: controls.filter(c => c.effectiveness === 'not_tested').length,
  }
  const recent = overlay.audit.filter(a => !a.action.startsWith('auth.') && !a.action.startsWith('auditlog.') && a.action !== 'evidence.downloaded')
    .map(({ id, at, actorName, action, entityType, entityId, detail }) => ({ id, at, actorName, action, entityType, entityId, detail }))
  d.activity = [...recent, ...d.activity].slice(0, 25)
  return d
})

route('GET', '/api/reports/board-pack', (ctx) => {
  const user = requirePerm(ctx, 'reports:board')
  return { ...read<Row>(user, '/api/reports/board-pack'), generatedAt: nowIso(), generatedBy: user.name }
})

route('GET', '/api/search', (ctx) => {
  const user = requireUser(ctx)
  const q = String(ctx.query.q ?? '').trim().toLowerCase()
  if (q.length < 2) return []
  const hit = (...v: unknown[]) => v.some(x => typeof x === 'string' && x.toLowerCase().includes(q))
  const out: { domain: string, ref: string, title: string, to: string }[] = []
  const add = (perm: Permission, resource: string, domain: string, limit: number, title: (r: Row) => string, to: (r: Row) => string, fields: (r: Row) => unknown[]) => {
    if (!can(user.role, perm)) return
    const rows = [...createdOf(resource), ...idx(resource).values()].map(r => applyPatch(resource, r)).filter(r => visible(user, r) && hit(...fields(r)))
    rows.slice(0, limit).forEach(r => out.push({ domain, ref: r.ref, title: title(r), to: to(r) }))
  }
  add('alerts:read', 'alerts', 'Alert', 6, r => r.title, r => `/alerts?id=${r.id}`, r => [r.title, r.ref])
  add('risk:read', 'risks', 'Risk', 5, r => r.title, r => `/risks?id=${r.id}`, r => [r.title, r.ref])
  add('controls:read', 'controls', 'Control', 5, r => r.name, r => `/controls?id=${r.id}`, r => [r.name, r.ref])
  add('compliance:read', 'obligations', 'Obligation', 5, r => r.title, r => `/compliance?id=${r.id}`, r => [r.title, r.framework])
  add('governance:read', 'policies', 'Policy', 4, r => r.title, r => `/governance?id=${r.id}`, r => [r.title])
  add('tprm:read', 'suppliers', 'Supplier', 4, r => r.name, r => `/tprm?id=${r.id}`, r => [r.name])
  add('incidents:read', 'incidents', 'Incident', 4, r => r.title, r => `/incidents?id=${r.id}`, r => [r.title, r.ref])
  add('aml:read', 'cases', 'AML case', 4, r => `${r.type} case`, r => `/aml?case=${r.id}`, r => [r.ref])
  return out.slice(0, 20)
})

// ── Alerts ──────────────────────────────────────────────────────────────────────────────────

function enrichAlert(a: Row): Row {
  a.assigneeName = userName(a.assigneeId)
  a.escalationLabel = escalationPathLabel(a.escalationPath)
  a.currentLevelLabel = ESCALATION_TARGETS[a.escalationPath[a.escalationLevel] as EscalationTarget]?.label ?? null
  a.slaLabel = slaLabel(a.sla)
  return a
}

function alertsFor(user: DemoUser): Row[] {
  const seen = new Set<number>()
  const base = [...(captured<Row[]>(user.email, '/api/alerts?view=all') ?? []), ...(captured<Row[]>(user.email, '/api/alerts?view=closed') ?? [])]
    .filter(a => !seen.has(a.id) && seen.add(a.id))
  return listView('alerts', base, user).map(enrichAlert)
}

route('GET', '/api/alerts', (ctx) => {
  const user = requirePerm(ctx, 'alerts:read')
  const view = ctx.query.view ?? 'open'
  const cutoff = Date.now() - 30 * DAY
  return alertsFor(user)
    .filter(a => view === 'closed' ? a.status === 'closed'
      : view === 'all' ? a.status !== 'closed' || Date.parse(a.closedAt) >= cutoff
        : OPEN_STATUSES.includes(a.status))
    .filter(a => !ctx.query.severity || a.severity === ctx.query.severity)
    .sort((x, y) => Date.parse(y.detectedAt) - Date.parse(x.detectedAt))
})

route('GET', '/api/alerts/:id', (ctx) => {
  const user = requirePerm(ctx, 'alerts:read')
  const id = Number(ctx.params[0])
  let a = captured<Row>(user.email, `/api/alerts/${id}`)
  if (!a) {
    const made = createdOf('alerts').find(r => r.id === id)
    if (!made || !visible(user, made)) fail(404, 'Alert not found')
    const rule = made.ruleId ? current('rules', made.ruleId) : null
    a = { ...made, events: [], evidence: [], rule: rule && { id: rule.id, name: rule.name, description: rule.description, dataSource: rule.dataSource } }
  }
  enrichAlert(applyPatch('alerts', a))
  a.events = [...a.events, ...eventsOf(`alerts:${id}`)]
  a.closedByName = userName(a.closedById)
  a.escalationSteps = a.escalationPath.map((t: EscalationTarget, i: number) => ({ label: ESCALATION_TARGETS[t].label, reached: i <= a!.escalationLevel, current: i === a!.escalationLevel }))
  const lastRequest = [...a.events].reverse().find((e: Row) => e.type === 'closure_requested')
  a.canSignOff = can(user.role, 'alerts:close') && a.status === 'pending_closure' && lastRequest?.actorId !== user.id
  a.evidence = evidenceFor('alert', id)
  return a
})

function alertEvent(alertId: number, user: DemoUser | null, type: string, note?: string | null) {
  addEvent(`alerts:${alertId}`, { id: nextId(), alertId, at: nowIso(), actorId: user?.id ?? null, actorName: user?.name ?? 'CCM Engine', type, note: note ?? null })
  changed.add('alert')
}

function resolveTarget(target: EscalationTarget, alert: Row): { label: string, userIds: number[] } {
  const def = ESCALATION_TARGETS[target]
  let ids: number[] = []
  if (def.dynamic === 'owner') ids = alert.ownerUserId ? [alert.ownerUserId] : []
  else if (def.dynamic === 'bu_manager') ids = allUsers().filter(u => u.role === 'BU_MANAGER' && u.businessUnit === alert.businessUnit).map(u => u.id)
  else if (def.role) ids = usersWithRole(def.role).map(u => u.id)
  if (!ids.length) ids = usersWithRole('CRO').map(u => u.id)
  return { label: def.label, userIds: ids }
}

async function createAlert(input: Row, actor: DemoUser | null): Promise<Row> {
  const now = nowIso()
  const path = input.escalationPath as EscalationTarget[]
  const first = path[0] ? resolveTarget(path[0], input) : null
  const sla = input.sla as SlaSpec
  const row = create('alerts', {
    id: nextId(), ref: nextRef('alerts', 'ALT-000001'), source: input.source, ruleId: input.ruleId ?? null, dedupeKey: input.dedupeKey ?? null,
    entityType: input.entityType ?? null, entityId: input.entityId ?? null, title: input.title, detail: input.detail ?? null, module: input.module,
    businessUnit: input.businessUnit ?? null, severity: input.severity, status: 'open', assigneeId: first?.userIds[0] ?? null,
    ownerUserId: input.ownerUserId ?? null, escalationPath: path, escalationLevel: 0, sla, detectedAt: now,
    ackDueAt: new Date(Date.now() + ACK_MINUTES[input.severity as Severity] * 60_000).toISOString(),
    dueAt: computeDueAt(sla, new Date()).toISOString(), overdue: false, remediationPlan: null, closureEvidence: null,
    closureRequestedAt: null, closedAt: null, closedById: null, falsePositive: false,
  })
  alertEvent(row.id, actor, 'detected', input.source === 'manual' ? 'Raised manually' : `Raised by ${input.ruleId ?? (input.source === 'kri' ? 'KRI monitor' : 'action monitor')}`)
  if (first) {
    alertEvent(row.id, null, 'routed', `Routed to ${first.label}`)
    first.userIds.forEach(id => notifyUser(id, { severity: row.severity, title: `${row.severity.toUpperCase()} alert ${row.ref}`, body: row.title, link: `/alerts?id=${row.id}` }))
  }
  await audit(actor, 'alert.created', 'alert', row.id, { ref: row.ref, ruleId: row.ruleId, severity: row.severity })
  return row
}

route('POST', '/api/alerts', async (ctx) => {
  const user = requirePerm(ctx, 'alerts:act')
  const b = ctx.body
  minLength(b.title, 5, 'Title must be at least 5 characters')
  required(b, 'module', 'severity', 'slaHours')
  if (!b.escalationPath?.length) fail(400, 'Choose at least one escalation step')
  const businessUnit = ROLES[user.role].scope === 'bu' ? user.businessUnit : b.businessUnit ?? null
  return createAlert({
    source: 'manual', title: b.title.trim(), detail: b.detail || null, module: b.module, severity: b.severity, businessUnit,
    sla: { type: 'minutes', minutes: Math.round(Number(b.slaHours) * 60) }, escalationPath: b.escalationPath,
  }, user)
})

route('POST', '/api/alerts/bulk-assign', async (ctx) => {
  const user = requirePerm(ctx, 'alerts:act')
  const { ids, assigneeId } = ctx.body as { ids: number[], assigneeId: number }
  const name = userName(assigneeId)
  if (!name) fail(400, 'Unknown user')
  const targets = ids.map(id => current('alerts', id)).filter((a): a is Row => !!a && a.status !== 'closed' && visible(user, a))
  for (const a of targets) {
    patch('alerts', a.id, { assigneeId })
    alertEvent(a.id, user, 'assigned', `Bulk-assigned to ${name}`)
  }
  if (targets.length) notifyUser(assigneeId, { severity: 'info', title: `${targets.length} alerts assigned to you`, link: '/alerts' })
  await audit(user, 'alert.bulk_assigned', 'alert', null, { refs: targets.map(a => a.ref), assignee: name })
  return { updated: targets.length }
})

route('POST', '/api/alerts/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const user = requirePerm(ctx, action === 'sign-off' ? 'alerts:close' : 'alerts:act')
  const id = Number(ctx.params[0])
  const alert = mustFind('alerts', id, 'Alert')
  if (!visible(user, alert)) fail(404, 'Alert not found')
  if (alert.status === 'closed') fail(409, 'Alert is already closed')
  const input = ctx.body
  const now = nowIso()
  let p: Row = {}
  let note: string | undefined
  switch (action) {
    case 'acknowledge':
      if (alert.acknowledgedAt) fail(409, 'Already acknowledged')
      p = { acknowledgedAt: now, status: alert.status === 'open' ? 'acknowledged' : alert.status, assigneeId: alert.assigneeId ?? user.id }
      break
    case 'assign': {
      const name = userName(input.assigneeId)
      if (!name) fail(400, 'Unknown user')
      p = { assigneeId: input.assigneeId }
      note = `Assigned to ${name}`
      notifyUser(input.assigneeId, { severity: alert.severity, title: `Alert ${alert.ref} assigned to you`, body: alert.title, link: `/alerts?id=${id}` })
      break
    }
    case 'note':
      note = minLength(input.note, 1, 'Write a note')
      break
    case 'remediation':
      note = minLength(input.plan, 5, 'Describe the remediation plan (at least 5 characters)')
      p = { remediationPlan: note, status: 'investigating', acknowledgedAt: alert.acknowledgedAt ?? now }
      break
    case 'escalate': {
      const reason = minLength(input.reason, 3, 'Give a reason for escalating')
      const next = Math.min(alert.escalationLevel + 1, alert.escalationPath.length - 1)
      if (next === alert.escalationLevel) fail(409, 'Already at the top of the escalation path')
      const target = resolveTarget(alert.escalationPath[next], alert)
      target.userIds.forEach(uid => notifyUser(uid, { severity: alert.severity, title: `Escalated: ${alert.ref}`, body: alert.title, link: `/alerts?id=${id}` }))
      p = { escalationLevel: next, status: 'escalated', assigneeId: target.userIds[0] ?? alert.assigneeId }
      note = `Escalated to ${target.label}: ${reason}`
      break
    }
    case 'request-closure': {
      const evidence = minLength(input.evidence, 5, 'Describe the closure evidence (at least 5 characters)')
      p = { status: 'pending_closure', closureEvidence: evidence, falsePositive: !!input.falsePositive, closureRequestedAt: now, acknowledgedAt: alert.acknowledgedAt ?? now }
      note = `${input.falsePositive ? 'False positive. ' : ''}${evidence}`
      notifyRole('CCO', { severity: 'info', title: `Closure sign-off requested: ${alert.ref}`, body: alert.title, link: `/alerts?id=${id}` })
      break
    }
    case 'sign-off': {
      if (alert.status !== 'pending_closure') fail(409, 'Closure has not been requested')
      const history = [...(captured<Row>(user.email, `/api/alerts/${id}`)?.events ?? []), ...eventsOf(`alerts:${id}`)]
      const request = history.reverse().find(e => e.type === 'closure_requested')
      if (request?.actorId === user.id) fail(403, 'Four-eyes rule: you requested this closure, so another authorised user must sign it off')
      p = input.decision === 'approve'
        ? { status: 'closed', closedAt: now, closedById: user.id, overdue: false }
        : { status: 'investigating', closureRequestedAt: null }
      note = input.note || undefined
      break
    }
    default:
      fail(404, 'Unknown action')
  }
  const type = ({ acknowledge: 'acknowledged', assign: 'assigned', note: 'note', remediation: 'remediation_plan', escalate: 'escalated', 'request-closure': 'closure_requested', 'sign-off': input.decision === 'approve' ? 'closed' : 'closure_rejected' } as Record<string, string>)[action]!
  if (Object.keys(p).length) patch('alerts', id, p)
  alertEvent(id, user, type, note)
  await audit(user, `alert.${type}`, 'alert', id, { ref: alert.ref, ...(note ? { note: note.slice(0, 200) } : {}) })
  return current('alerts', id)
})

// ── Control monitoring ──────────────────────────────────────────────────────────────────────

route('GET', '/api/ccm/rules', (ctx) => {
  const user = requirePerm(ctx, 'ccm:read')
  const d = read(user, '/api/ccm/rules')
  const open = allAlerts().filter(a => OPEN_STATUSES.includes(a.status))
  d.rules = listView('rules', d.rules, user)
    .map(r => ({ ...r, slaLabel: slaLabel(r.sla), escalationLabel: escalationPathLabel(r.escalationPath), openAlerts: open.filter(a => a.ruleId === r.id).length }))
    .sort((a, b) => a.id.localeCompare(b.id))
  return d
})

route('POST', '/api/ccm/rules', async (ctx) => {
  const user = requirePerm(ctx, 'ccm:manage')
  const b = ctx.body
  minLength(b.name, 3, 'Name must be at least 3 characters')
  minLength(b.description, 3, 'Description must be at least 3 characters')
  const catalogue = capturedAny<Row>('/api/ccm/rules')?.catalogue ?? {}
  if (!(b.evaluator in catalogue)) fail(400, 'Unknown evaluator')
  const ids = [...idx('rules').keys(), ...createdOf('rules').map(r => r.id)].map(id => Number(String(id).slice(4)))
  const id = `CCM-${String(Math.max(0, ...ids) + 1).padStart(3, '0')}`
  const rule = create('rules', {
    id, name: b.name.trim(), description: b.description.trim(), evaluator: b.evaluator, params: b.params ?? {}, severity: b.severity, sla: b.sla,
    escalationPath: b.escalationPath, module: b.module, dataSource: b.dataSource, enabled: true, state: 'pass', lastRunAt: nowIso(),
    lastBreaches: 0, lastWarnings: 0, openAlerts: 0, evaluatorLabel: catalogue[b.evaluator]?.label ?? b.evaluator,
  })
  changed.add('ccm')
  await audit(user, 'ccm.rule_created', 'ccm_rule', id, { name: rule.name, evaluator: rule.evaluator })
  return rule
})

route('PATCH', '/api/ccm/rules/:id', async (ctx) => {
  const user = requirePerm(ctx, 'ccm:manage')
  const id = ctx.params[0]!
  const { reason, ...p } = ctx.body
  minLength(reason, 5, 'Give a reason for the change (recorded in the audit log)')
  const rule = mustFind('rules', id, 'Rule')
  const next: Row = { ...p, ...(p.params ? { params: { ...rule.params, ...p.params } } : {}), state: p.enabled === false ? 'unknown' : rule.state }
  patch('rules', id, next)
  changed.add('ccm')
  const changedFields = Object.fromEntries(Object.keys(p).map(k => [k, { from: rule[k], to: next[k] }]))
  await audit(user, 'ccm.rule_changed', 'ccm_rule', id, { reason, changed: changedFields })
  return current('rules', id)
})

route('POST', '/api/ccm/run', async (ctx) => {
  const user = requirePerm(ctx, 'ccm:manage')
  const started = performance.now()
  const rules = [...idx('rules').values(), ...createdOf('rules')]
  for (const r of rules) patch('rules', r.id, { lastRunAt: nowIso() })
  const summary = { rulesEvaluated: rules.length, alertsCreated: 0, escalated: 0, durationMs: Math.max(18, Math.round(performance.now() - started)), at: nowIso() }
  changed.add('ccm')
  await audit(user, 'ccm.manual_run', 'ccm', null, summary)
  return summary
})

// ── Risks, KRIs, controls ───────────────────────────────────────────────────────────────────

const kriStatus = (k: Row) => (k.value >= k.red ? 'red' : k.value >= k.amber ? 'amber' : 'green')

function riskView(r: Row): Row {
  const controls: Row[] = r.controlIds
    ? r.controlIds.map((id: number) => current('controls', id)).filter(Boolean)
    : (r.controls ?? []).map((c: Row) => current('controls', c.id) ?? c)
  r.controls = controls.map(c => ({ id: c.id, ref: c.ref, name: c.name, effectiveness: c.effectiveness, ccmRuleId: c.ccmRuleId ?? null }))
  r.kris = (r.kris ?? []).map((k: Row) => { const cur = applyPatch('kris', k); return { ...cur, status: kriStatus(cur) } })
  Object.assign(r, scoreRisk(r.impact, r.likelihood, r.controls.map((c: Row) => c.effectiveness as ControlEffectiveness)))
  r.ownerName = userName(r.ownerId) ?? 'Unknown'
  delete r.controlIds
  return r
}

route('GET', '/api/risks', (ctx) => {
  const user = requirePerm(ctx, 'risk:read')
  return listView('risks', read(user, '/api/risks'), user).filter(r => r.status === 'open').map(riskView)
})

route('POST', '/api/risks', async (ctx) => {
  const user = requirePerm(ctx, 'risk:write')
  const b = ctx.body
  minLength(b.title, 3, 'Title must be at least 3 characters')
  required(b, 'category', 'ownerId', 'impact', 'likelihood', 'treatment')
  const row = create('risks', {
    id: nextId(), ref: nextRef('risks', 'R-001'), title: b.title.trim(), description: b.description ?? null, category: b.category, ownerId: b.ownerId,
    businessUnit: b.businessUnit ?? null, impact: b.impact, likelihood: b.likelihood, treatment: b.treatment, treatmentPlan: b.treatmentPlan ?? null,
    treatmentDueAt: toIso(b.treatmentDueAt), source: 'manual', status: 'open', escalatedToBoard: false, lastReviewedAt: nowIso(), createdAt: nowIso(),
    controlIds: b.controlIds ?? [], kris: [],
  })
  await audit(user, 'risk.created', 'risk', row.id, { ref: row.ref, inherent: row.impact * row.likelihood })
  return row
})

route('PATCH', '/api/risks/:id', async (ctx) => {
  const user = requirePerm(ctx, 'risk:write')
  const id = Number(ctx.params[0])
  const before = mustFind('risks', id, 'Risk')
  const { reviewed, ...p } = ctx.body
  if ('treatmentDueAt' in p) p.treatmentDueAt = toIso(p.treatmentDueAt)
  patch('risks', id, { ...p, ...(reviewed ? { lastReviewedAt: nowIso() } : {}) })
  if (p.escalatedToBoard && !before.escalatedToBoard) {
    notifyRole('BOARD', { severity: 'high', title: `Material risk escalated: ${before.ref}`, body: before.title, link: `/risks?id=${id}` })
  }
  await audit(user, 'risk.updated', 'risk', id, { ref: before.ref, fields: Object.keys(p), reviewed: !!reviewed })
  return current('risks', id)
})

route('PATCH', '/api/kris/:id', async (ctx) => {
  const user = requirePerm(ctx, 'risk:write')
  const id = Number(ctx.params[0])
  const kri = mustFind('kris', id, 'KRI')
  const p = ctx.body as { value?: number, amber?: number, red?: number }
  const amber = p.amber ?? kri.amber
  const red = p.red ?? kri.red
  if (amber >= red) fail(400, 'Amber threshold must be below red')
  const value = p.value ?? kri.value
  patch('kris', id, { ...p, history: p.value !== undefined ? [...kri.history.slice(-11), p.value] : kri.history, updatedAt: nowIso() })
  await audit(user, 'kri.updated', 'kri', id, { name: kri.name, from: kri.value, to: value })

  // Same as the CCM engine: crossing the red limit raises a KRI alert to the risk owner.
  if (value >= red && kri.value < red) {
    const risk = current('risks', kri.riskId)
    await createAlert({
      source: 'kri', module: 'Enterprise Risk', severity: 'high', sla: { type: 'business_days', days: 1 }, escalationPath: ['CONTROL_OWNER', 'CRO'],
      dedupeKey: `KRI:kri:${id}`, entityType: 'risk', entityId: kri.riskId, businessUnit: risk?.businessUnit ?? null, ownerUserId: risk?.ownerId ?? null,
      title: `KRI limit breached: ${kri.name}`, detail: `${value}${kri.unit} against red limit ${red}${kri.unit} (risk ${risk?.ref}: ${risk?.title}).`,
    }, null)
  }
  return current('kris', id)
})

function controlView(c: Row): Row {
  c.ownerName = userName(c.ownerId)
  const rule = c.ccmRuleId ? current('rules', c.ccmRuleId) : null
  c.rule = rule ? { id: rule.id, state: rule.state, name: rule.name } : null
  c.recentTests ??= []
  c.passRate = c.recentTests.length ? Math.round(100 * c.recentTests.filter((t: Row) => t.result === 'pass').length / c.recentTests.length) : null
  c.riskIds ??= []
  c.assuranceCount ??= 0
  return c
}

route('GET', '/api/controls', (ctx) => {
  const user = requirePerm(ctx, 'controls:read')
  return listView('controls', read(user, '/api/controls'), user).map(controlView)
})

route('POST', '/api/controls', async (ctx) => {
  const user = requirePerm(ctx, 'controls:write')
  const { riskIds, ...b } = ctx.body
  minLength(b.name, 3, 'Name must be at least 3 characters')
  required(b, 'ownerId', 'businessUnit', 'type', 'frequency')
  const row = create('controls', {
    id: nextId(), ref: nextRef('controls', 'C-001'), name: b.name.trim(), description: b.description ?? null, ownerId: b.ownerId, businessUnit: b.businessUnit,
    type: b.type, frequency: b.frequency, automated: !!b.automated, keyControl: !!b.keyControl, effectiveness: 'not_tested', ccmRuleId: b.ccmRuleId ?? null,
    lastTestedAt: null, createdAt: nowIso(), recentTests: [], riskIds: riskIds ?? [], assuranceCount: 0,
  })
  for (const riskId of riskIds ?? []) {
    const risk = current('risks', riskId)
    if (risk) patch('risks', riskId, { controlIds: [...(risk.controlIds ?? risk.controls.map((c: Row) => c.id)), row.id] })
  }
  await audit(user, 'control.created', 'control', row.id, { ref: row.ref })
  return row
})

route('PATCH', '/api/controls/:id', async (ctx) => {
  const user = requirePerm(ctx, 'controls:write')
  const id = Number(ctx.params[0])
  const control = mustFind('controls', id, 'Control')
  const { riskIds: _ignored, ...p } = ctx.body
  patch('controls', id, p)
  await audit(user, 'control.updated', 'control', id, { ref: control.ref, fields: Object.keys(p) })
  return current('controls', id)
})

route('POST', '/api/controls/:id/tests', async (ctx) => {
  const user = requirePerm(ctx, 'controls:write')
  const id = Number(ctx.params[0])
  const control = mustFind('controls', id, 'Control')
  const notes = minLength(ctx.body.notes, 3, 'Add test notes (at least 3 characters)')
  const result = ctx.body.result as 'pass' | 'fail'
  const tests = [{ id: nextId(), at: nowIso(), result, method: 'manual', notes }, ...(control.recentTests ?? [])].slice(0, 6)
  const recent = tests.slice(0, 3)
  const effectiveness: ControlEffectiveness = recent.every(t => t.result === 'pass') ? 'effective'
    : recent.length >= 2 && recent[0]!.result === 'fail' && recent[1]!.result === 'fail' ? 'ineffective'
      : 'partially_effective'
  patch('controls', id, { recentTests: tests, effectiveness, lastTestedAt: tests[0]!.at })
  await audit(user, 'control.tested', 'control', id, { ref: control.ref, result, effectiveness })
  return { effectiveness }
})

// ── Compliance and policies ─────────────────────────────────────────────────────────────────

route('GET', '/api/obligations', (ctx) => {
  const user = requirePerm(ctx, 'compliance:read')
  return listView('obligations', read(user, '/api/obligations'), user)
    .map(o => ({ ...o, ownerName: userName(o.ownerId), evidenceCount: (o.evidenceCount ?? 0) + evidenceCount('obligation', o.id) }))
})

route('POST', '/api/obligations', async (ctx) => {
  const user = requirePerm(ctx, 'compliance:write')
  const b = ctx.body
  minLength(b.title, 3, 'Title must be at least 3 characters')
  required(b, 'sourceType', 'framework', 'jurisdiction', 'ownerId', 'status')
  const row = create('obligations', {
    id: nextId(), ref: nextRef('obligations', 'OB-001'), title: b.title.trim(), sourceType: b.sourceType, framework: b.framework, regulator: b.regulator ?? null,
    jurisdiction: b.jurisdiction, businessUnit: b.businessUnit ?? null, ownerId: b.ownerId, status: b.status,
    lastAssessedAt: b.status === 'not_assessed' ? null : nowIso(), nextReviewAt: toIso(b.nextReviewAt), controlIds: b.controlIds ?? [], policyIds: [], evidenceCount: 0,
  })
  await audit(user, 'obligation.created', 'obligation', row.id, { ref: row.ref, framework: row.framework })
  return row
})

route('PATCH', '/api/obligations/:id', async (ctx) => {
  const user = requirePerm(ctx, 'compliance:write')
  const id = Number(ctx.params[0])
  const before = mustFind('obligations', id, 'Obligation')
  const p = { ...ctx.body }
  if ('nextReviewAt' in p) p.nextReviewAt = toIso(p.nextReviewAt)
  if (p.status && p.status !== before.status) p.lastAssessedAt = nowIso()
  patch('obligations', id, p)
  await audit(user, 'obligation.updated', 'obligation', id, { ref: before.ref, fields: Object.keys(p) })
  return current('obligations', id)
})

route('GET', '/api/regulatory-reports', (ctx) => {
  const user = requirePerm(ctx, 'compliance:read')
  return listView('reports', read(user, '/api/regulatory-reports'), user)
    .map(r => ({ ...r, state: r.submittedAt ? 'submitted' : r.state, submittedByName: userName(r.submittedById) }))
})

route('POST', '/api/regulatory-reports/:id/:action', async (ctx) => {
  const action = ctx.params[1]
  const user = requirePerm(ctx, action === 'submit' ? 'regreport:submit' : 'compliance:write')
  const id = Number(ctx.params[0])
  const report = mustFind('reports', id, 'Report')
  if (report.submittedAt) fail(409, 'Already submitted')
  if (action === 'prepare') patch('reports', id, { preparedAt: nowIso() })
  else if (action === 'submit') {
    const reference = minLength(ctx.body.reference, 3, 'Enter the regulator reference')
    patch('reports', id, { submittedAt: nowIso(), submittedById: user.id, reference, preparedAt: report.preparedAt ?? nowIso() })
  }
  else fail(404, 'Unknown action')
  await audit(user, `regulatory_report.${action === 'prepare' ? 'prepared' : 'submitted'}`, 'regulatory_report', id, { name: report.name, reference: ctx.body.reference })
  return current('reports', id)
})

route('GET', '/api/policies', (ctx) => {
  const user = requirePerm(ctx, 'governance:read')
  return listView('policies', read(user, '/api/policies'), user).map((p) => {
    const ackBy: number[] = p.ackBy ?? []
    const mine = ackBy.includes(user.id)
    const { ackBy: _drop, ...rest } = p
    return {
      ...rest,
      ownerName: userName(p.ownerId), approvedByName: userName(p.approvedById),
      ackCount: (p.ackCount ?? 0) + ackBy.filter(id => id !== user.id || !p.acknowledgedByMe).length,
      acknowledgedByMe: p.acknowledgedByMe || mine,
    }
  }).sort((a, b) => a.ref.localeCompare(b.ref))
})

route('GET', '/api/policies/:id/history', (ctx) => {
  const user = requirePerm(ctx, 'governance:read')
  const id = Number(ctx.params[0])
  const base = captured<Row[]>(user.email, `/api/policies/${id}/history`) ?? []
  return [...base, ...eventsOf(`policies:${id}`)].sort((a, b) => Date.parse(b.at) - Date.parse(a.at))
})

function policyBody(b: Row) {
  minLength(b.title, 3, 'Title must be at least 3 characters')
  required(b, 'ownerId', 'jurisdiction', 'reviewDate')
  return { ...b, reviewDate: toIso(b.reviewDate) }
}

route('POST', '/api/policies', async (ctx) => {
  const user = requirePerm(ctx, 'governance:write')
  const b = policyBody(ctx.body)
  const row = create('policies', {
    id: nextId(), ref: nextRef('policies', 'POL-001'), title: b.title.trim(), summary: b.summary ?? null, ownerId: b.ownerId, version: 1, status: 'draft',
    jurisdiction: b.jurisdiction, frameworks: b.frameworks ?? [], reviewDate: b.reviewDate, approvedById: null, approvedAt: null, createdAt: nowIso(),
    ackCount: 0, ackTarget: allUsers().length, acknowledgedByMe: false, reviewOverdue: false,
    riskIds: b.riskIds ?? [], controlIds: b.controlIds ?? [], obligationIds: b.obligationIds ?? [],
  })
  addEvent(`policies:${row.id}`, { id: nextId(), policyId: row.id, version: 1, status: 'draft', note: 'Draft created', actorId: user.id, at: nowIso(), actorName: user.name })
  await audit(user, 'policy.created', 'policy', row.id, { ref: row.ref })
  return row
})

route('PATCH', '/api/policies/:id', async (ctx) => {
  const user = requirePerm(ctx, 'governance:write')
  const id = Number(ctx.params[0])
  const p = mustFind('policies', id, 'Policy')
  if (p.status !== 'draft') fail(409, 'Only draft policies can be edited. Start a new version first.')
  patch('policies', id, policyBody({ ...p, ...ctx.body }))
  await audit(user, 'policy.updated', 'policy', id, { ref: p.ref })
  return current('policies', id)
})

route('POST', '/api/policies/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const needed: Permission = action === 'acknowledge' ? 'governance:read' : action === 'approve' || action === 'reject' ? 'policy:approve' : 'governance:write'
  const user = requirePerm(ctx, needed)
  const id = Number(ctx.params[0])
  const p = mustFind('policies', id, 'Policy')
  const note: string | undefined = ctx.body.note || undefined
  if (['reject', 'new-version', 'retire'].includes(action)) minLength(note, 3, 'Add a note (at least 3 characters)')

  const transition = (from: string[], to: string, extra: Row = {}) => {
    if (!from.includes(p.status)) fail(409, `Cannot ${action} a policy that is ${p.status.replace('_', ' ')}`)
    patch('policies', id, { status: to, ...extra })
    addEvent(`policies:${id}`, { id: nextId(), policyId: id, version: extra.version ?? p.version, status: to, note: note ?? null, actorId: user.id, at: nowIso(), actorName: user.name })
  }
  switch (action) {
    case 'submit':
      transition(['draft'], 'in_review')
      notifyRole('CCO', { severity: 'info', title: `Policy ready for approval: ${p.ref}`, body: p.title, link: `/governance?id=${id}` })
      break
    case 'approve':
      if (p.ownerId === user.id) fail(403, 'Policy owners cannot approve their own policy')
      transition(['in_review'], 'approved', { approvedById: user.id, approvedAt: nowIso() })
      break
    case 'reject':
      transition(['in_review'], 'draft')
      notifyUser(p.ownerId, { severity: 'info', title: `Policy returned: ${p.ref}`, body: note, link: `/governance?id=${id}` })
      break
    case 'new-version':
      transition(['approved'], 'draft', { version: p.version + 1, approvedById: null, approvedAt: null })
      break
    case 'retire':
      transition(['approved', 'draft'], 'retired')
      break
    case 'acknowledge':
      if (p.status !== 'approved') fail(409, 'Only approved policies can be acknowledged')
      patch('policies', id, { ackBy: [...new Set([...(p.ackBy ?? []), user.id])] })
      break
    default:
      fail(404, 'Unknown action')
  }
  await audit(user, `policy.${action}`, 'policy', id, { ref: p.ref, note })
  return current('policies', id)
})

// ── Assurance, audit, self-assessment ───────────────────────────────────────────────────────

route('GET', '/api/assurance', (ctx) => {
  const user = requirePerm(ctx, 'assurance:read')
  const d = read(user, '/api/assurance')
  d.activities = listView('activities', d.activities, user).map(a => ({ ...a, ownerName: userName(a.ownerId) }))
  return d
})

route('POST', '/api/assurance/activities', async (ctx) => {
  const user = requirePerm(ctx, 'assurance:write')
  const b = ctx.body
  minLength(b.title, 3, 'Title must be at least 3 characters')
  required(b, 'line', 'provider', 'activityType', 'plannedStart', 'plannedEnd', 'ownerId')
  if (Date.parse(b.plannedEnd) < Date.parse(b.plannedStart)) fail(400, 'End must be after start')
  const row = create('activities', {
    id: nextId(), line: b.line, provider: b.provider, title: b.title.trim(), activityType: b.activityType, plannedStart: toIso(b.plannedStart),
    plannedEnd: toIso(b.plannedEnd), status: 'planned', rating: null, ownerId: b.ownerId, controlIds: b.controlIds ?? [],
  })
  await audit(user, 'assurance.planned', 'assurance_activity', row.id, { title: row.title, line: row.line })
  return row
})

route('PATCH', '/api/assurance/activities/:id', async (ctx) => {
  const user = requirePerm(ctx, 'assurance:write')
  const id = Number(ctx.params[0])
  mustFind('activities', id, 'Activity')
  if (ctx.body.status === 'complete' && !ctx.body.rating) fail(400, 'A completed activity needs a rating')
  patch('activities', id, ctx.body)
  await audit(user, 'assurance.updated', 'assurance_activity', id, { ...ctx.body })
  return current('activities', id)
})

route('GET', '/api/audit', (ctx) => {
  const user = requirePerm(ctx, 'audit:read')
  const d = read(user, '/api/audit')
  const findings = listView('findings', d.findings, user).map(f => ({
    ...f, ownerName: userName(f.ownerId), overdue: f.status !== 'closed' && Date.parse(f.dueAt) < Date.now(),
  }))
  d.findings = findings
  d.engagements = listView('engagements', d.engagements, user).map(e => ({
    ...e, leadName: userName(e.leadId),
    findings: findings.filter(f => f.engagementId === e.id).length,
    openFindings: findings.filter(f => f.engagementId === e.id && f.status !== 'closed').length,
  }))
  return d
})

route('POST', '/api/audit/engagements', async (ctx) => {
  const user = requirePerm(ctx, 'audit:write')
  const b = ctx.body
  minLength(b.title, 3, 'Title must be at least 3 characters')
  required(b, 'businessUnit', 'leadId', 'plannedStart', 'plannedEnd')
  if (Date.parse(b.plannedEnd) < Date.parse(b.plannedStart)) fail(400, 'End must be after start')
  const year = new Date(b.plannedStart).getFullYear()
  const row = create('engagements', {
    id: nextId(), ref: nextRef('engagements', `IA-${year}-01`), title: b.title.trim(), scope: b.scope ?? null, businessUnit: b.businessUnit, leadId: b.leadId,
    status: 'planned', plannedStart: toIso(b.plannedStart), plannedEnd: toIso(b.plannedEnd), rating: null,
  })
  await audit(user, 'audit.engagement_planned', 'audit_engagement', row.id, { ref: row.ref })
  return row
})

route('PATCH', '/api/audit/engagements/:id', async (ctx) => {
  const user = requirePerm(ctx, 'audit:write')
  const id = Number(ctx.params[0])
  const e = mustFind('engagements', id, 'Engagement')
  if (ctx.body.status === 'closed' && !ctx.body.rating) fail(400, 'Closing an engagement requires an overall rating')
  patch('engagements', id, ctx.body)
  await audit(user, 'audit.engagement_updated', 'audit_engagement', id, { ref: e.ref, ...ctx.body })
  return current('engagements', id)
})

route('POST', '/api/audit/findings', async (ctx) => {
  const user = requirePerm(ctx, 'audit:write')
  const { raiseIncident, ...b } = ctx.body
  minLength(b.title, 5, 'Title must be at least 5 characters')
  required(b, 'engagementId', 'rating', 'ownerId', 'dueAt')
  const engagement = mustFind('engagements', b.engagementId, 'Engagement')
  const row = create('findings', {
    id: nextId(), ref: nextRef('findings', `F-${new Date().getFullYear()}-001`), engagementId: b.engagementId, title: b.title.trim(), rating: b.rating,
    ownerId: b.ownerId, businessUnit: engagement.businessUnit, managementResponse: null, dueAt: toIso(b.dueAt), status: 'open', closedAt: null,
    closureEvidence: null, engagementRef: engagement.ref,
  })
  notifyUser(b.ownerId, { severity: b.rating === 'high' ? 'high' : 'info', title: `Audit finding ${row.ref} assigned`, body: row.title, link: '/audit' })
  if (raiseIncident) {
    createIncident(user, {
      title: row.title, type: 'audit_finding', severity: b.rating === 'high' ? 'high' : b.rating, businessUnit: engagement.businessUnit, ownerId: b.ownerId,
      occurredAt: nowIso(), description: `Raised from audit finding ${row.ref} (${engagement.ref}).`,
    })
  }
  await audit(user, 'audit.finding_raised', 'audit_finding', row.id, { ref: row.ref, rating: row.rating })
  return row
})

route('PATCH', '/api/audit/findings/:id', async (ctx) => {
  const user = requirePerm(ctx, 'audit:read')
  const id = Number(ctx.params[0])
  const f = mustFind('findings', id, 'Finding')
  const b = ctx.body
  const isAuditor = can(user.role, 'audit:write')
  if (!isAuditor && f.ownerId !== user.id) fail(403, 'Only the finding owner or Internal Audit can update this finding')
  if (b.dueAt && !isAuditor) fail(403, 'Only Internal Audit can change agreed due dates')
  if (b.close && !isAuditor) fail(403, 'Closure is validated by Internal Audit')
  if (b.close) minLength(b.close.evidence, 10, 'Describe the closure evidence (at least 10 characters)')
  patch('findings', id, {
    ...(b.managementResponse ? { managementResponse: minLength(b.managementResponse, 5, 'Response must be at least 5 characters') } : {}),
    ...(b.dueAt ? { dueAt: toIso(b.dueAt) } : {}),
    ...(b.close ? { status: 'closed', closedAt: nowIso(), closureEvidence: b.close.evidence } : {}),
  })
  await audit(user, b.close ? 'audit.finding_closed' : 'audit.finding_updated', 'audit_finding', id, { ref: f.ref, dueChanged: !!b.dueAt })
  return current('findings', id)
})

route('GET', '/api/csa', (ctx) => {
  const user = requirePerm(ctx, 'csa:read')
  return listView('csa', read(user, '/api/csa'), user).map((a) => {
    const control = current('controls', a.controlId)
    const effectiveness = control?.effectiveness ?? a.control.effectiveness
    return {
      ...a, control: { ...a.control, effectiveness }, submittedByName: userName(a.submittedById),
      overdue: !a.submittedAt && Date.parse(a.dueAt) < Date.now(),
      divergent: !!a.result && effectiveness !== 'not_tested' && a.result !== effectiveness,
    }
  })
})

route('POST', '/api/csa/:id/submit', async (ctx) => {
  const user = requirePerm(ctx, 'csa:submit')
  const id = Number(ctx.params[0])
  const row = mustFind('csa', id, 'Assessment')
  if (!visible(user, row)) fail(404, 'Assessment not found')
  if (row.submittedAt) fail(409, 'Already submitted')
  const { result, comment } = ctx.body
  if (result !== 'effective' && (!comment || comment.trim().length < 10)) fail(400, 'Explain why the control is not fully effective')
  patch('csa', id, { result, comment: comment || null, submittedAt: nowIso(), submittedById: user.id })
  await audit(user, 'csa.submitted', 'csa', id, { result, period: row.period })
  return current('csa', id)
})

// ── Financial crime ─────────────────────────────────────────────────────────────────────────

const casesNow = () => [...createdOf('cases'), ...idx('cases').values()].map(c => applyPatch('cases', structuredClone(c)))

function caseView(c: Row): Row {
  c.analystName = userName(c.analystId)
  c.sarDaysElapsed = c.status === 'sar_decided' && c.sarDecisionAt ? Math.floor((Date.now() - Date.parse(c.sarDecisionAt)) / DAY) : c.sarDaysElapsed ?? null
  return c
}

route('GET', '/api/aml/overview', (ctx) => {
  const user = requirePerm(ctx, 'aml:read')
  const d = read(user, '/api/aml/overview')
  const cases = casesNow()
  d.openCases = cases.filter(c => c.status !== 'closed' && c.status !== 'sar_submitted').length
  d.highScore = cases.filter(c => c.status !== 'closed' && c.status !== 'sar_submitted' && c.score >= 70).length
  d.pipeline = d.pipeline.map((s: Row) => ({ ...s, count: cases.filter(c => c.status === s.key).length }))
  d.transactions24h.pendingHighValue = listView('transactions', captured<Row[]>(user.email, '/api/aml/transactions?pending=1') ?? [], user)
    .filter(t => t.screeningStatus === 'pending').length
  return d
})

route('GET', '/api/aml/cases', (ctx) => {
  const user = requirePerm(ctx, 'aml:read')
  return listView('cases', read(user, '/api/aml/cases'), user).map(caseView)
})

route('GET', '/api/aml/cases/:id', (ctx) => {
  const user = requirePerm(ctx, 'aml:read')
  const id = Number(ctx.params[0])
  let c = captured<Row>(user.email, `/api/aml/cases/${id}`)
  if (!c) {
    const made = createdOf('cases').find(r => r.id === id)
    if (!made) fail(404, 'Case not found')
    const customer = current('customers', made.customerId)
    const tx = made.transactionId ? current('transactions', made.transactionId) : null
    c = { ...made, customer: customer ?? made.customer, transactions: tx ? [tx] : [], flaggedTransactionId: made.transactionId }
  }
  caseView(applyPatch('cases', c))
  c.transactions = (c.transactions ?? []).map((t: Row) => applyPatch('transactions', t))
  c.evidence = evidenceFor('aml_case', id)
  c.canSubmitSar = can(user.role, 'sar:submit')
  return c
})

route('POST', '/api/aml/cases/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const user = requirePerm(ctx, action === 'sar-decision' || action === 'sar-submit' ? 'sar:submit' : 'aml:write')
  const id = Number(ctx.params[0])
  const c = mustFind('cases', id, 'Case')
  if (c.status === 'closed' || c.status === 'sar_submitted') fail(409, 'Case is closed')
  const b = ctx.body
  const now = nowIso()
  let p: Row
  switch (action) {
    case 'assign':
      p = { analystId: b.analystId, status: c.status === 'new' ? 'under_review' : c.status }
      notifyUser(b.analystId, { severity: 'info', title: `AML case ${c.ref} assigned to you`, link: `/aml?case=${id}` })
      break
    case 'escalate':
      minLength(b.note, 5, 'Add an escalation note (at least 5 characters)')
      p = { status: 'escalated' }
      notifyRole('MLRO', { severity: 'high', title: `AML case escalated: ${c.ref}`, body: b.note, link: `/aml?case=${id}` })
      break
    case 'dismiss':
      p = { status: 'closed', outcome: minLength(b.outcome, 10, 'Record the outcome (at least 10 characters)'), closedAt: now }
      break
    case 'sar-decision': {
      const grounds = minLength(b.grounds, 20, 'State the grounds for suspicion (at least 20 characters)')
      const customer = current('customers', c.customerId) ?? c.customer
      const draft = [
        `SUBJECT: ${customer.name} (${customer.ref}), ${customer.type ?? 'customer'}, ${customer.country}`,
        `CASE: ${c.ref}, opened ${String(c.openedAt).slice(0, 10)}, risk score ${c.score}/100`,
        `RISK FACTORS: ${c.scoreFactors.map((f: Row) => f.factor).join('; ')}`,
        `GROUNDS FOR SUSPICION: ${grounds}`,
      ].join('\n')
      p = { status: 'sar_decided', sarDecisionAt: now, sarDraft: draft }
      break
    }
    case 'sar-submit':
      if (c.status !== 'sar_decided') fail(409, 'Record a SAR decision first')
      p = { status: 'sar_submitted', sarSubmittedAt: now, ncaReference: minLength(b.ncaReference, 5, 'Enter the NCA reference (at least 5 characters)'), closedAt: now }
      break
    default:
      fail(404, 'Unknown action')
  }
  patch('cases', id, p)
  // Tipping-off risk: SAR content is never written to the audit detail, only the fact of the action.
  await audit(user, `aml.${action.replace('-', '_')}`, 'aml_case', id, { ref: c.ref, status: p.status ?? c.status })
  return current('cases', id)
})

function openCase(fields: Row): Row {
  const customer = current('customers', fields.customerId)!
  return create('cases', {
    id: nextId(), ref: nextRef('cases', 'AML-0001'), transactionId: null, alertId: null, sarDecisionAt: null, sarDraft: null, sarSubmittedAt: null,
    ncaReference: null, outcome: null, closedAt: null, openedAt: nowIso(), analystId: null, sarDaysElapsed: null, ...fields,
    customer: { id: customer.id, ref: customer.ref, name: customer.name, riskRating: customer.riskRating, country: customer.country },
  })
}

route('GET', '/api/aml/transactions', (ctx) => {
  const user = requirePerm(ctx, 'aml:read')
  return listView('transactions', read(user, '/api/aml/transactions?pending=1'), user).filter(t => !ctx.query.pending || t.screeningStatus === 'pending')
})

route('POST', '/api/aml/transactions/:id/screen', async (ctx) => {
  const user = requirePerm(ctx, 'aml:write')
  const id = Number(ctx.params[0])
  const tx = mustFind('transactions', id, 'Transaction')
  if (tx.screeningStatus !== 'pending') fail(409, 'Transaction already screened')
  const { result } = ctx.body
  const note = minLength(ctx.body.note, 5, 'Record the screening rationale (at least 5 characters)')
  patch('transactions', id, { screeningStatus: result })
  let caseRef: string | null = null
  if (result === 'hit') {
    const customer = current('customers', tx.customerId)!
    const { score, factors } = scoreAml({ ...customer as any, counterpartyCountryRisk: countryRiskOf(tx.counterpartyCountry), amount: tx.amount })
    caseRef = openCase({ customerId: tx.customerId, transactionId: tx.id, type: 'transaction', score, scoreFactors: factors, status: 'under_review', analystId: user.id }).ref
    // The held payment no longer breaches CCM-002 once screened.
    for (const a of allAlerts().filter(a => a.ruleId === 'CCM-002' && a.status !== 'closed' && a.entityId === id)) patch('alerts', a.id, { overdue: false })
  }
  await audit(user, 'transaction.screened', 'transaction', id, { ref: tx.ref, result, caseRef, note })
  return { result, caseRef }
})

route('GET', '/api/aml/customers', (ctx) => {
  const user = requirePerm(ctx, 'aml:read')
  return listView('customers', read(user, '/api/aml/customers'), user)
})

route('POST', '/api/aml/customers/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const user = requirePerm(ctx, 'aml:write')
  const id = Number(ctx.params[0])
  const c = mustFind('customers', id, 'Customer')
  const b = ctx.body
  let p: Row = {}
  switch (action) {
    case 'verify-kyc':
      minLength(b.note, 5, 'Add a verification note (at least 5 characters)')
      if (c.sanctionsStatus === 'pending') fail(409, 'Sanctions screening must complete before KYC verification')
      p = { kycStatus: 'verified', kycVerifiedAt: nowIso() }
      break
    case 'refresh-kyc':
      minLength(b.note, 5, 'Add a note (at least 5 characters)')
      p = { kycVerifiedAt: nowIso(), kycExpired: false }
      break
    case 'start-edd':
      p = { eddStatus: 'in_progress' }
      break
    case 'complete-edd': {
      minLength(b.sourceOfFunds, 10, 'Describe the source of funds (at least 10 characters)')
      if (b.uboVerified !== true) fail(400, 'Confirm beneficial ownership has been verified')
      if (b.seniorApproverId === user.id) fail(403, 'Senior approval must come from someone other than the preparer')
      const approver = allUsers().find(u => u.id === b.seniorApproverId)
      if (!approver || !['MLRO', 'CCO', 'CRO'].includes(approver.role)) fail(400, 'Approver must be MLRO, CCO or CRO')
      p = { eddStatus: 'complete', eddCompletedAt: nowIso() }
      notifyUser(approver.id, { severity: 'info', title: `EDD approval recorded for ${c.ref}`, link: '/aml' })
      break
    }
    case 'rescreen': {
      const score = scoreAml({ ...c as any, sanctionsStatus: b.result })
      p = { sanctionsStatus: b.result, riskRating: b.result === 'clear' ? c.riskRating : ratingForScore(score.score) }
      if (p.riskRating === 'high' && c.eddStatus === 'not_required') p.eddStatus = 'required'
      if (b.result !== 'clear') {
        patch('customers', id, p)
        openCase({ customerId: id, type: 'sanctions', score: score.score, scoreFactors: score.factors, status: b.result === 'confirmed_match' ? 'escalated' : 'new' })
        if (b.result === 'confirmed_match') notifyRole('MLRO', { severity: 'critical', title: `Confirmed sanctions match: ${c.ref}`, link: '/aml' })
      }
      break
    }
    default:
      fail(404, 'Unknown action')
  }
  patch('customers', id, p)
  await audit(user, `customer.${action.replace('-', '_')}`, 'customer', id, { ref: c.ref, ...(action === 'rescreen' ? { result: b.result } : {}) })
  return current('customers', id)
})

// ── Third parties ───────────────────────────────────────────────────────────────────────────

route('GET', '/api/suppliers', (ctx) => {
  const user = requirePerm(ctx, 'tprm:read')
  return listView('suppliers', read(user, '/api/suppliers'), user).map(s => ({
    ...s, ownerName: userName(s.ownerId),
    reviewOverdue: !!s.nextReviewAt && s.status !== 'exited' && Date.parse(s.nextReviewAt) < Date.now(),
  }))
})

route('POST', '/api/suppliers', async (ctx) => {
  const user = requirePerm(ctx, 'tprm:write')
  const { regulatoryCritical, ...b } = ctx.body
  minLength(b.name, 2, 'Name is required')
  minLength(b.services, 2, 'Describe the services')
  if (!b.country || String(b.country).length !== 2) fail(400, 'Use a two-letter country code')
  const tier = classifySupplier({ ...b, countryRisk: countryRiskOf(b.country), questionnaireScore: null, regulatoryCritical: !!regulatoryCritical })
  const row = create('suppliers', {
    id: nextId(), ref: nextRef('suppliers', 'SUP-301'), name: b.name.trim(), services: b.services.trim(), ownerId: b.ownerId, country: String(b.country).toUpperCase(),
    tier, dataAccess: !!b.dataAccess, annualSpend: Number(b.annualSpend) || 0, ddStatus: 'in_progress', questionnaireScore: null, sanctionsStatus: 'pending',
    monitoringFlags: [], status: 'onboarding', lastReviewAt: null, nextReviewAt: null, contractRenewalAt: toIso(b.contractRenewalAt), dpaInPlace: !!b.dpaInPlace,
    renewalSoon: false,
  })
  await audit(user, 'supplier.onboarded', 'supplier', row.id, { ref: row.ref, tier })
  return row
})

route('POST', '/api/suppliers/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const user = requirePerm(ctx, 'tprm:write')
  const id = Number(ctx.params[0])
  const s = mustFind('suppliers', id, 'Supplier')
  const b = ctx.body
  const now = nowIso()
  let p: Row
  switch (action) {
    case 'complete-dd': {
      if (b.sanctionsStatus === 'confirmed_match') fail(409, 'Confirmed sanctions match. Onboarding cannot proceed. Escalate to the MLRO.')
      const tier = classifySupplier({ ...s as any, countryRisk: countryRiskOf(s.country), questionnaireScore: b.questionnaireScore, regulatoryCritical: !!b.regulatoryCritical })
      p = { ddStatus: 'complete', questionnaireScore: b.questionnaireScore, sanctionsStatus: b.sanctionsStatus, tier, status: 'active', lastReviewAt: now, nextReviewAt: addMonths(now, SUPPLIER_REVIEW_MONTHS[tier]) }
      break
    }
    case 'review':
      minLength(b.note, 5, 'Add a review note (at least 5 characters)')
      p = { lastReviewAt: now, nextReviewAt: addMonths(now, SUPPLIER_REVIEW_MONTHS[s.tier as keyof typeof SUPPLIER_REVIEW_MONTHS]), ...(b.questionnaireScore !== undefined ? { questionnaireScore: b.questionnaireScore } : {}) }
      break
    case 'flag':
      p = { monitoringFlags: [...s.monitoringFlags, minLength(b.flag, 3, 'Describe the flag (at least 3 characters)')] }
      if (s.tier === 'critical' || s.tier === 'high') notifyRole('CCO', { severity: 'high', title: `Supplier alert: ${s.name}`, body: b.flag, link: `/tprm?id=${id}` })
      break
    case 'clear-flags':
      minLength(b.note, 5, 'Add a note (at least 5 characters)')
      p = { monitoringFlags: [] }
      break
    case 'exit':
      minLength(b.note, 5, 'Add an exit note (at least 5 characters)')
      p = { status: 'exited', nextReviewAt: null }
      break
    default:
      fail(404, 'Unknown action')
  }
  patch('suppliers', id, p)
  await audit(user, `supplier.${action.replace('-', '_')}`, 'supplier', id, { ref: s.ref, ...b })
  return current('suppliers', id)
})

// ── Incidents and actions ───────────────────────────────────────────────────────────────────

const actionsNow = () => [...createdOf('actions'), ...idx('actions').values()].map(a => applyPatch('actions', structuredClone(a)))
const actionView = (a: Row) => ({ ...a, ownerName: userName(a.ownerId), overdue: a.status !== 'done' && Date.parse(a.dueAt) < Date.now() })

function incidentView(i: Row): Row {
  const own = actionsNow().filter(a => a.sourceType === 'incident' && a.sourceId === i.id)
  i.ownerName = userName(i.ownerId)
  i.reportedByName = userName(i.reportedById)
  i.actionsTotal = own.length
  i.actionsOpen = own.filter(a => a.status !== 'done').length
  i.notificationOverdue = !!i.notifiable && !i.notifiedAt && !!i.notificationDueAt && Date.parse(i.notificationDueAt) < Date.now()
  return i
}

function createIncident(user: DemoUser, b: Row): Row {
  return create('incidents', {
    id: nextId(), ref: nextRef('incidents', 'INC-0001'), title: b.title, description: b.description ?? null, type: b.type, severity: b.severity,
    status: 'open', businessUnit: b.businessUnit, reportedById: user.id, ownerId: b.ownerId ?? null, occurredAt: toIso(b.occurredAt), reportedAt: nowIso(),
    rcaMethod: null, rootCause: null, notifiable: false, regulator: null, notificationDueAt: null, notifiedAt: null, lessonsLearned: null,
    sourceAlertId: b.sourceAlertId ?? null, closedAt: null, closedById: null, riskIds: [], controlIds: [], obligationIds: [],
  })
}

route('GET', '/api/incidents', (ctx) => {
  const user = requirePerm(ctx, 'incidents:read')
  return listView('incidents', read(user, '/api/incidents'), user).map(incidentView)
})

route('GET', '/api/incidents/:id', (ctx) => {
  const user = requirePerm(ctx, 'incidents:read')
  const id = Number(ctx.params[0])
  let i = captured<Row>(user.email, `/api/incidents/${id}`)
  if (!i) {
    const made = createdOf('incidents').find(r => r.id === id)
    if (!made || !visible(user, made)) fail(404, 'Incident not found')
    i = { ...made, risks: [], controls: [], obligations: [] }
  }
  incidentView(applyPatch('incidents', i))
  const pick = (resource: string, ids: number[] | undefined, fallback: Row[], fields: string[]) =>
    ids ? ids.map(x => current(resource, x)).filter(Boolean).map(r => Object.fromEntries(fields.map(f => [f, r![f]]))) : fallback
  i.risks = pick('risks', i.riskIds, i.risks, ['id', 'ref', 'title'])
  i.controls = pick('controls', i.controlIds, i.controls, ['id', 'ref', 'name'])
  i.obligations = pick('obligations', i.obligationIds, i.obligations, ['id', 'ref', 'title'])
  delete i.riskIds
  delete i.controlIds
  delete i.obligationIds
  i.actions = actionsNow().filter(a => a.sourceType === 'incident' && a.sourceId === id).map(actionView)
  i.closedByName = userName(i.closedById)
  i.evidence = evidenceFor('incident', id)
  i.notificationRule = NOTIFICATION_RULES[i.type] ?? null
  return i
})

route('POST', '/api/incidents', async (ctx) => {
  const user = requirePerm(ctx, 'incidents:write')
  const b = ctx.body
  minLength(b.title, 3, 'Title must be at least 3 characters')
  required(b, 'type', 'severity', 'businessUnit', 'occurredAt')
  const businessUnit = ROLES[user.role].scope === 'bu' ? user.businessUnit : b.businessUnit
  const row = createIncident(user, { ...b, title: b.title.trim(), businessUnit })
  if (b.sourceAlertId) alertEvent(b.sourceAlertId, user, 'incident_raised', `Incident ${row.ref} raised from this alert`)
  if (b.severity === 'critical' || b.severity === 'high') {
    notifyRole('CRO', { severity: b.severity, title: `${b.severity.toUpperCase()} incident logged: ${row.ref}`, body: row.title, link: `/incidents?id=${row.id}` })
  }
  await audit(user, 'incident.logged', 'incident', row.id, { ref: row.ref, severity: row.severity, type: row.type })
  return row
})

route('PATCH', '/api/incidents/:id', async (ctx) => {
  const user = requirePerm(ctx, 'incidents:write')
  const id = Number(ctx.params[0])
  const i = mustFind('incidents', id, 'Incident')
  if (!visible(user, i)) fail(404, 'Incident not found')
  if (i.status === 'closed') fail(409, 'Incident is closed')
  const { notified, ...p } = ctx.body
  const type = p.type ?? i.type
  if (p.notifiable === true && !i.notifiable) {
    const rule = NOTIFICATION_RULES[type]!
    p.regulator = rule.regulator
    p.notificationDueAt = new Date(Date.parse(i.reportedAt) + rule.hours * 3_600_000).toISOString()
    notifyRole('CCO', { severity: 'high', title: `Regulatory notification required: ${i.ref} → ${rule.regulator}`, body: rule.basis, link: `/incidents?id=${id}` })
  }
  if (p.notifiable === false) Object.assign(p, { regulator: null, notificationDueAt: null })
  if (notified) p.notifiedAt = nowIso()
  patch('incidents', id, p)
  await audit(user, notified ? 'incident.regulator_notified' : 'incident.updated', 'incident', id, { ref: i.ref, fields: Object.keys(p) })
  return current('incidents', id)
})

route('POST', '/api/incidents/:id/close', async (ctx) => {
  const user = requirePerm(ctx, 'incidents:write')
  if (ROLES[user.role].scope === 'bu') fail(403, 'Incident closure requires second-line sign-off')
  const id = Number(ctx.params[0])
  const i = mustFind('incidents', id, 'Incident')
  const lessonsLearned = minLength(ctx.body.lessonsLearned, 10, 'Record the lessons learned (at least 10 characters)')
  if (!i.rootCause) fail(409, 'Record the root cause before closing')
  if (i.notifiable && !i.notifiedAt) fail(409, `Regulatory notification to ${i.regulator} is outstanding`)
  const open = actionsNow().filter(a => a.sourceType === 'incident' && a.sourceId === id && a.status !== 'done')
  if (open.length) fail(409, `${open.length} corrective ${open.length === 1 ? 'action is' : 'actions are'} still open`)
  patch('incidents', id, { status: 'closed', lessonsLearned, closedAt: nowIso(), closedById: user.id })
  await audit(user, 'incident.closed', 'incident', id, { ref: i.ref })
  return current('incidents', id)
})

route('GET', '/api/actions', (ctx) => {
  const user = requirePerm(ctx, 'incidents:read')
  return listView('actions', read(user, '/api/actions'), user).map(actionView)
})

route('POST', '/api/actions', async (ctx) => {
  const user = requirePerm(ctx, 'incidents:write')
  const b = ctx.body
  minLength(b.title, 5, 'Title must be at least 5 characters')
  required(b, 'sourceType', 'sourceId', 'ownerId', 'dueAt')
  const row = create('actions', {
    id: nextId(), ref: nextRef('actions', 'ACT-0001'), title: b.title.trim(), sourceType: b.sourceType, sourceId: b.sourceId, ownerId: b.ownerId,
    dueAt: toIso(b.dueAt), status: 'open', completedAt: null, evidenceNote: null,
  })
  notifyUser(b.ownerId, { severity: 'info', title: `Corrective action assigned: ${row.ref}`, body: row.title, link: '/incidents?tab=actions' })
  await audit(user, 'action.created', 'action', row.id, { ref: row.ref, source: `${b.sourceType}:${b.sourceId}` })
  return row
})

route('PATCH', '/api/actions/:id', async (ctx) => {
  const user = requirePerm(ctx, 'incidents:read')
  const id = Number(ctx.params[0])
  const a = mustFind('actions', id, 'Action')
  if (a.ownerId !== user.id && !can(user.role, 'incidents:write')) fail(403, 'Only the action owner can update it')
  const { status, evidenceNote } = ctx.body
  if (status === 'done' && (!evidenceNote || evidenceNote.trim().length < 5)) fail(400, 'Closure evidence is required')
  patch('actions', id, { status, ...(evidenceNote ? { evidenceNote } : {}), completedAt: status === 'done' ? nowIso() : null })
  await audit(user, 'action.updated', 'action', id, { ref: a.ref, status })
  return current('actions', id)
})

// ── ESG, people, access, programme, integrations ────────────────────────────────────────────

route('GET', '/api/esg', (ctx) => {
  const user = requirePerm(ctx, 'esg:read')
  return listView('esg', read(user, '/api/esg'), user)
    .map(m => ({ ...m, ownerName: userName(m.ownerId), evidenceCount: (m.evidenceCount ?? 0) + evidenceCount('esg_metric', m.id) }))
})

route('PATCH', '/api/esg/:id', async (ctx) => {
  const user = requirePerm(ctx, 'esg:write')
  const id = Number(ctx.params[0])
  const m = mustFind('esg', id, 'Metric')
  const evidenceNote = minLength(ctx.body.evidenceNote, 5, 'Cite the evidence source for this figure')
  const value: number | null = ctx.body.value ?? null
  let status = 'no_data'
  if (value !== null && m.target !== null) {
    const better = m.lowerIsBetter ? value <= m.target : value >= m.target
    const gap = Math.abs(value - m.target) / Math.max(Math.abs(m.target), 1)
    status = better ? 'on_track' : gap <= 0.1 ? 'at_risk' : 'off_track'
  }
  else if (value !== null) status = 'on_track'
  patch('esg', id, { value, evidenceNote, status })
  await audit(user, 'esg.metric_updated', 'esg_metric', id, { name: m.name, from: m.value, to: value, status })
  return current('esg', id)
})

route('GET', '/api/vetting', (ctx) => {
  const user = requirePerm(ctx, 'hr:read')
  return listView('vetting', read(user, '/api/vetting'), user).map(e => ({
    ...e, atRisk: e.vettingStatus !== 'cleared' && !e.terminatedAt && e.daysToStart !== null && e.daysToStart <= 7,
  }))
})

route('PATCH', '/api/vetting/:id', async (ctx) => {
  const user = requirePerm(ctx, 'hr:write')
  const id = Number(ctx.params[0])
  const e = mustFind('vetting', id, 'Employee')
  const b = ctx.body
  patch('vetting', id, {
    ...(b.vettingStatus ? { vettingStatus: b.vettingStatus, vettingClearedAt: b.vettingStatus === 'cleared' ? nowIso() : null } : {}),
    ...(b.startDate ? { startDate: toIso(b.startDate) } : {}),
    ...(b.terminated ? { terminatedAt: nowIso() } : {}),
  })
  if (b.vettingStatus === 'failed') notifyRole('CISO', { severity: 'high', title: `Vetting failed: ${e.ref}`, body: 'Ensure no system access is provisioned.', link: '/vetting' })
  await audit(user, 'employee.vetting_updated', 'employee', id, { ref: e.ref, from: e.vettingStatus, to: b.vettingStatus ?? e.vettingStatus })
  return current('vetting', id)
})

route('GET', '/api/security/access', (ctx) => {
  const user = requirePerm(ctx, 'security:read')
  return listView('access', read(user, '/api/security/access'), user).map((a) => {
    const age = a.lastReviewedAt ? Math.floor((Date.now() - Date.parse(a.lastReviewedAt)) / DAY) : null
    return { ...a, reviewedByName: userName(a.reviewedById), reviewAgeDays: age, overdue: a.privileged ? age === null || age > 90 : a.overdue }
  })
})

route('POST', '/api/security/access/:id/review', async (ctx) => {
  const user = requirePerm(ctx, 'security:write')
  const id = Number(ctx.params[0])
  const acct = mustFind('access', id, 'Account')
  const { decision } = ctx.body
  const note = minLength(ctx.body.note, 5, 'Add a review note (at least 5 characters)')
  patch('access', id, { lastReviewedAt: nowIso(), reviewedById: user.id, ...(decision === 'revoke' ? { privileged: false, groups: [] } : {}) })
  await audit(user, `access.${decision}`, 'access_account', id, { account: acct.account, system: acct.system, note })
  return current('access', id)
})

route('GET', '/api/programme', (ctx) => {
  const user = requirePerm(ctx, 'programme:read')
  return listView('phases', read(user, '/api/programme'), user).map((p) => {
    const tasks = p.tasks.map((t: Row) => applyPatch('tasks', t))
    return { ...p, tasks, progress: tasks.length ? Math.round(100 * tasks.filter((t: Row) => t.done).length / tasks.length) : 0 }
  })
})

route('PATCH', '/api/programme/tasks/:id', async (ctx) => {
  const user = requirePerm(ctx, 'programme:write')
  const id = Number(ctx.params[0])
  const task = mustFind('tasks', id, 'Task')
  const phase = mustFind('phases', task.phaseId, 'Phase')
  if (phase.status === 'complete') fail(409, 'Phase already signed off')
  const done = !!ctx.body.done
  patch('tasks', id, { done, doneAt: done ? nowIso() : null })
  if (done && phase.status === 'not_started') patch('phases', phase.id, { status: 'in_progress' })
  await audit(user, 'programme.task_updated', 'programme_task', id, { title: task.title, done })
  return current('tasks', id)
})

route('POST', '/api/programme/phases/:id/signoff', async (ctx) => {
  const user = requirePerm(ctx, 'programme:write')
  const id = Number(ctx.params[0])
  const phase = mustFind('phases', id, 'Phase')
  if (phase.status === 'complete') fail(409, 'Already signed off')
  const prev = current('phases', id - 1)
  if (prev && prev.status !== 'complete') fail(409, `Phase ${prev.id} (${prev.name}) must be signed off first`)
  const open = phase.tasks.map((t: Row) => current('tasks', t.id)!).filter((t: Row) => !t.done)
  if (open.length) fail(409, `${open.length} ${open.length === 1 ? 'task' : 'tasks'} outstanding`)
  patch('phases', id, { status: 'complete', signedOffAt: nowIso() })
  await audit(user, 'programme.phase_signed_off', 'programme_phase', id, { name: phase.name, gate: phase.gate })
  return current('phases', id)
})

route('GET', '/api/integrations', (ctx) => {
  const user = requirePerm(ctx, 'integrations:read')
  const d = read(user, '/api/integrations')
  d.integrations = listView('integrations', d.integrations, user)
    .map(i => ({ ...i, minutesSinceSync: i.lastSyncAt ? Math.floor((Date.now() - Date.parse(i.lastSyncAt)) / 60_000) : null }))
  return d
})

route('POST', '/api/integrations/:id/sync', async (ctx) => {
  const user = requirePerm(ctx, 'integrations:manage')
  const id = ctx.params[0]!
  const row = mustFind('integrations', id, 'Integration')
  if (row.status === 'disconnected') fail(409, `${row.name} is disconnected. Check credentials in the connector configuration`)
  patch('integrations', id, { lastSyncAt: nowIso() })
  await audit(user, 'integration.sync_requested', 'integration', id, { name: row.name })
  return current('integrations', id)
})

// ── Administration ──────────────────────────────────────────────────────────────────────────

route('GET', '/api/admin/users', (ctx) => {
  const user = requirePerm(ctx, 'users:manage')
  return listView('users', read(user, '/api/admin/users'), user).map(({ password: _p, ...u }) => ({
    ...u, roleLabel: ROLES[u.role as RoleKey].label, locked: !!u.lockedUntil && Date.parse(u.lockedUntil) > Date.now(),
  }))
})

route('POST', '/api/admin/users', async (ctx) => {
  const admin = requirePerm(ctx, 'users:manage')
  const b = ctx.body
  const email = String(b.email ?? '').trim().toLowerCase()
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) fail(400, 'Enter a valid email address')
  minLength(b.name, 2, 'Name is required')
  if (!b.password || b.password.length < 12) fail(400, 'Use at least 12 characters')
  if (ROLES[b.role as RoleKey]?.scope === 'bu' && !b.businessUnit) fail(400, 'BU-scoped roles need a business unit')
  if (allUsers().some(u => u.email === email)) fail(409, 'A user with that email already exists')
  const row = create('users', {
    id: nextId(), email, name: b.name.trim(), role: b.role, businessUnit: b.businessUnit ?? null, password: b.password, mfaEnabled: false,
    failedLogins: 0, lockedUntil: null, active: true, lastLoginAt: null, createdAt: nowIso(),
  })
  await audit(admin, 'user.created', 'user', row.id, { email, role: b.role })
  return { id: row.id }
})

route('PATCH', '/api/admin/users/:id', async (ctx) => {
  const admin = requirePerm(ctx, 'users:manage')
  const id = Number(ctx.params[0])
  const { unlock, resetMfa, ...p } = ctx.body
  if (id === admin.id && (p.active === false || (p.role && p.role !== admin.role))) fail(409, 'You cannot deactivate or change the role of your own account')
  const before = mustFind('users', id, 'User')
  patch('users', id, { ...p, ...(unlock ? { lockedUntil: null, failedLogins: 0 } : {}), ...(resetMfa ? { mfaEnabled: false } : {}) })
  await audit(admin, 'user.updated', 'user', id, { email: before.email, ...p, unlock: !!unlock, resetMfa: !!resetMfa, previousRole: before.role })
  return { ok: true }
})

route('GET', '/api/users/lookup', (ctx) => {
  const user = requireUser(ctx)
  const base = read<Row[]>(user, '/api/users/lookup')
  const made = createdOf('users').map(u => ({ id: u.id, name: u.name, role: u.role, businessUnit: u.businessUnit, roleLabel: ROLES[u.role as RoleKey].label }))
  return [...base, ...made].map(u => ({ ...u, ...(current('users', u.id)?.role ? { role: current('users', u.id)!.role } : {}) }))
})

route('GET', '/api/admin/audit-log', (ctx) => {
  requirePerm(ctx, 'auditlog:read')
  const q = String(ctx.query.q ?? '').trim().toLowerCase()
  const before = Number(ctx.query.before) || Infinity
  const rows = [...overlay.audit, ...fixtureAuditLog()]
    .filter(r => r.id < before)
    .filter(r => !q || [r.action, r.actorName, r.entityType, r.entityId, JSON.stringify(r.detail)].some(v => String(v ?? '').toLowerCase().includes(q)))
  const page = rows.slice(0, 50)
  return { rows: page, nextBefore: rows.length > 50 ? page.at(-1)!.id : null }
})

route('GET', '/api/admin/audit-log/verify', async (ctx) => {
  const user = requirePerm(ctx, 'auditlog:read')
  const checked = overlay.audit.length + fixtureAuditLog().length
  await audit(user, 'auditlog.verified', 'audit_log', null, { valid: true, brokenAt: null, checked })
  saveOverlay(['activity'])
  return { valid: true, brokenAt: null, checked }
})

route('GET', '/api/notifications', (ctx) => {
  const user = requireUser(ctx)
  const read_ = new Set(overlay.read[user.id] ?? [])
  const base = captured<Row>(user.email, '/api/notifications')?.items ?? []
  const items = [...overlay.notifications.filter(n => n.userId === user.id), ...base]
    .map(n => ({ ...n, readAt: n.readAt ?? (read_.has(n.id) ? nowIso() : null) }))
    .slice(0, 50)
  return { items, unread: items.filter(n => !n.readAt).length }
})

route('POST', '/api/notifications/read', (ctx) => {
  const user = requireUser(ctx)
  const ids: number[] | undefined = ctx.body.ids
  const mine = [...overlay.notifications.filter(n => n.userId === user.id), ...(captured<Row>(user.email, '/api/notifications')?.items ?? [])].map(n => n.id)
  overlay.read[user.id] = [...new Set([...(overlay.read[user.id] ?? []), ...(ids?.length ? ids : mine)])]
  changed.add('notification')
  return { ok: true }
})

// ── Evidence ────────────────────────────────────────────────────────────────────────────────

const EVIDENCE_WRITE: Record<string, Permission> = {
  alert: 'alerts:act', aml_case: 'aml:write', incident: 'incidents:write', audit_finding: 'audit:read', obligation: 'compliance:write',
  control: 'controls:write', csa: 'csa:submit', esg_metric: 'esg:write', policy: 'governance:write', supplier: 'tprm:write',
}

route('GET', '/api/evidence', (ctx) => {
  requireUser(ctx)
  return evidenceFor(ctx.query.entityType ?? '', ctx.query.entityId ?? '')
})

route('POST', '/api/evidence', async (ctx) => {
  const user = requireUser(ctx)
  const form = ctx.body as FormData
  const entityType = String(form.get('entityType') ?? '')
  const entityId = Number(form.get('entityId'))
  const file = form.get('file') as File | null
  const perm = EVIDENCE_WRITE[entityType]
  if (!perm || !Number.isInteger(entityId)) fail(400, 'Unknown evidence target')
  if (!can(user.role, perm)) fail(403, 'Your role cannot attach evidence here')
  if (!file || !file.size) fail(400, 'No file uploaded')
  if (file.size > 15 * 1024 * 1024) fail(413, 'File exceeds 15 MB limit')
  const row = {
    id: nextId(), entityType, entityId, filename: file.name.slice(0, 200), mime: file.type || 'application/octet-stream', size: file.size,
    sha256: await sha256(await file.arrayBuffer()), uploadedById: user.id, uploadedByName: user.name, uploadedAt: nowIso(),
  }
  ;(overlay.evidence[`${entityType}:${entityId}`] ??= []).unshift(row)
  evidenceBlobs.set(row.id, file)
  if (entityType === 'alert') alertEvent(entityId, user, 'evidence', `Evidence uploaded: ${row.filename}`)
  await audit(user, 'evidence.uploaded', entityType, entityId, { filename: row.filename, sha256: row.sha256 })
  return row
})

// ── Downloads (CSV exports and evidence files) ──────────────────────────────────────────────

const EXPORT_PERMS: Record<string, Permission> = {
  alerts: 'alerts:read', ccm: 'ccm:read', obligations: 'compliance:read', controls: 'controls:read', incidents: 'incidents:read', risks: 'risk:read', suppliers: 'tprm:read',
}

/** Resolves an `/api/...` download link to a file, or null if the link is not a download. */
export function download(path: string): { filename: string, blob: Blob } {
  const user = readSession()
  if (!user) fail(401, 'Authentication required')
  const exp = /^\/api\/export\/(\w+)$/.exec(path)
  if (exp) {
    const dataset = exp[1]!
    if (!EXPORT_PERMS[dataset] || !can(user.role, EXPORT_PERMS[dataset]!)) fail(403, 'Your role cannot export this register')
    const csv = captured<string>(user.email, `/api/export/${dataset}`) ?? ''
    return { filename: `oysterchecks-${dataset}-${new Date().toISOString().slice(0, 10)}.csv`, blob: new Blob([csv], { type: 'text/csv' }) }
  }
  const ev = /^\/api\/evidence\/(\d+)$/.exec(path)
  if (ev) {
    const id = Number(ev[1])
    const meta = Object.values(overlay.evidence).flat().find(e => e.id === id)
    if (!meta) fail(404, 'Evidence not found')
    const blob = evidenceBlobs.get(id)
      ?? new Blob([`${meta.filename}\nSHA-256 ${meta.sha256}\nUploaded by ${meta.uploadedByName} on ${meta.uploadedAt}\n\nThe file was attached in another browser tab. Demo files are kept only in the tab that uploaded them.\n`], { type: 'text/plain' })
    void audit(user, 'evidence.downloaded', meta.entityType, meta.entityId, { filename: meta.filename })
    return { filename: evidenceBlobs.has(id) ? meta.filename : `${meta.filename}.txt`, blob }
  }
  fail(404, 'Download not available in the demo')
}
