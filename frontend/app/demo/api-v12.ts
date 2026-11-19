// Demo API for the Version 1.2 capabilities: Perimeter Intelligence and regulatory change (with
// maker-checker approval), multi-channel transaction monitoring, Digital Assets Intelligence, the
// unified traceability chain and the internal delivery gates. Registered on import, alongside api.ts.

import { can, type Permission } from '#shared/utils/rbac'
import { scoreAml } from '#shared/utils/aml'
import { type DemoUser, capturedAny, fail } from './fixtures'
import { addEvent, applyPatch, create, createdOf, eventsOf, nextId, patch } from './state'
import {
  OPEN_STATUSES, allAlerts, audit, casesNow, current, evidenceFor, gateStatus, minLength, notifyRole, notifyUser, nowIso, openCase,
  requirePerm, riskView, route, searchProviders, toIso, userName,
} from './api'
import {
  CHANGES, COMPARISONS, GATES, JURISDICTION_PACKS, LEGAL_ENTITIES, MVP_JOURNEYS, OBLIGATION_SOURCE, SOURCES, TM_ALERTS, TM_CHANNELS,
  TM_SCENARIOS, TRANSFERS, TRAVEL_RULE, WALLETS, demoHash,
} from './extended'

type Row = any

/** Base records plus demo-created ones, with demo changes applied. */
function rowsOf(resource: string, base: Row[]): Row[] {
  const ids = new Set(base.map(r => String(r.id)))
  return [...createdOf(resource).filter(r => !ids.has(String(r.id))), ...structuredClone(base)].map(r => applyPatch(resource, r))
}
function findRow(resource: string, base: Row[], id: number | string, what: string): Row {
  return rowsOf(resource, base).find(r => String(r.id) === String(id)) ?? fail(404, `${what} not found`)
}

const entityName = (id: string) => LEGAL_ENTITIES.find(e => e.id === id)?.name ?? id
const maskName = (name: string) => name.split(' ').map(w => w[0] + '•'.repeat(Math.max(2, [...w].length - 1))).join(' ')
function customerOf(user: DemoUser, id: number | null) {
  const c = id ? current('customers', id) : null
  if (!c) return null
  const show = can(user.role, 'pii:view')
  return { id: c.id, ref: c.ref, name: show ? c.name : maskName(c.name), country: c.country, riskRating: c.riskRating, kycStatus: c.kycStatus, type: c.type }
}
const open = (status: string) => OPEN_STATUSES.includes(status)

// ── Executive view: exposure by legal entity and jurisdiction ───────────────────────────────

const allRisks = () => rowsOf('risks', capturedAny<Row[]>('/api/risks') ?? []).filter(r => r.status === 'open').map(riskView)
const allControls = () => rowsOf('controls', capturedAny<Row[]>('/api/controls') ?? [])

function entityExposure() {
  const changes = rowsOf('changes', CHANGES)
  const tmAlerts = rowsOf('tm_alerts', TM_ALERTS)
  return LEGAL_ENTITIES.map((e) => {
    const openChanges = changes.filter(c => c.status !== 'approved' && c.applicability.some((a: Row) => a.entityId === e.id && a.decision && a.decision !== 'not_applicable')).length
    const tmOpen = tmAlerts.filter(a => a.entityId === e.id && (a.status === 'new' || a.status === 'in_review')).length
    // The UK bank carries the demo's detailed records, so its figures are calculated live.
    const s = e.primary
      ? {
          openAlerts: allAlerts().filter(a => open(a.status)).length,
          highRisks: allRisks().filter(r => r.residual.band === 'high' || r.residual.band === 'critical').length,
          failingControls: allControls().filter(c => c.effectiveness === 'ineffective').length,
          openCases: casesNow().filter(c => c.status !== 'closed' && c.status !== 'sar_submitted').length,
          assurance: e.summary.assurance,
        }
      : e.summary
    return { id: e.id, name: e.name, jurisdiction: e.jurisdiction, country: e.country, regulators: e.regulators, activities: e.activities, ...s, openChanges, tmOpen }
  })
}

route('GET', '/api/perimeter/entities', (ctx) => {
  requirePerm(ctx, 'dashboard:exec')
  return entityExposure()
})

// ── Perimeter Intelligence ─────────────────────────────────────────────────────────────────

route('GET', '/api/perimeter/overview', (ctx) => {
  requirePerm(ctx, 'perimeter:read')
  const changes = rowsOf('changes', CHANGES)
  const active = JURISDICTION_PACKS.filter(p => p.status === 'active')
  const packs = JURISDICTION_PACKS.map(p => ({
    ...p,
    openChanges: changes.filter(c => c.jurisdiction === p.code && c.status !== 'approved').length,
    entities: LEGAL_ENTITIES.filter(e => e.jurisdiction === p.code || (p.code === 'EU' && e.jurisdiction === 'IE')).map(e => e.name),
  }))
  return {
    kpis: {
      activePacks: active.length,
      availablePacks: JURISDICTION_PACKS.length - active.length,
      sources: SOURCES.length,
      obligations: active.reduce((n, p) => n + p.obligations, 0),
      openChanges: changes.filter(c => c.status !== 'approved').length,
      awaitingApproval: changes.filter(c => c.status === 'pending_approval').length,
      unvalidated: changes.reduce((n, c) => n + c.extracted.filter((x: Row) => !x.validated).length, 0),
      freshness: Math.round(active.reduce((n, p) => n + p.freshnessDays, 0) / active.length),
      quality: Math.round(active.reduce((n, p) => n + p.quality, 0) / active.length),
      coverage: Math.round(active.reduce((n, p) => n + p.coverage, 0) / active.length),
    },
    packs,
    entities: entityExposure(),
    horizon: changes
      .filter(c => c.status !== 'approved' || (c.effectiveAt && Date.parse(c.effectiveAt) > Date.now()))
      .sort((a, b) => Date.parse(a.effectiveAt ?? a.publishedAt) - Date.parse(b.effectiveAt ?? b.publishedAt))
      .map(c => ({ id: c.id, ref: c.ref, title: c.title, jurisdiction: c.jurisdiction, effectiveAt: c.effectiveAt, publishedAt: c.publishedAt, status: c.status, priority: c.priority })),
    journeys: MVP_JOURNEYS,
  }
})

route('GET', '/api/perimeter/sources', (ctx) => {
  requirePerm(ctx, 'perimeter:read')
  return SOURCES.map(s => ({
    ...s,
    pack: JURISDICTION_PACKS.find(p => p.code === s.jurisdiction)?.name ?? s.jurisdiction,
    obligations: s.obligationIds.map(id => current('obligations', id)).filter(Boolean).map(o => ({ id: o!.id, ref: o!.ref, title: o!.title })),
  }))
})

route('GET', '/api/perimeter/comparisons', (ctx) => {
  requirePerm(ctx, 'perimeter:read')
  return COMPARISONS.map((c) => {
    const control = c.controlId ? current('controls', c.controlId) : null
    return {
      ...c,
      control: control ? { id: control.id, ref: control.ref, name: control.name, effectiveness: control.effectiveness } : null,
      rows: c.rows.map(r => ({ ...r, jurisdiction: JURISDICTION_PACKS.find(p => p.code === r.code)?.name ?? r.code })),
    }
  })
})

function changeSummary(c: Row) {
  return {
    id: c.id, ref: c.ref, title: c.title, issuer: c.issuer, jurisdiction: c.jurisdiction, publishedAt: c.publishedAt, effectiveAt: c.effectiveAt,
    priority: c.priority, status: c.status,
    extractedTotal: c.extracted.length, extractedValidated: c.extracted.filter((x: Row) => x.validated).length,
    applicableEntities: c.applicability.filter((a: Row) => a.decision === 'applicable' || a.decision === 'partially_applicable').length,
    assessedEntities: c.applicability.filter((a: Row) => a.decision).length,
    makerName: userName(c.makerId), approverName: userName(c.approverId),
  }
}

route('GET', '/api/perimeter/changes', (ctx) => {
  requirePerm(ctx, 'perimeter:read')
  return rowsOf('changes', CHANGES).map(changeSummary)
})

const EDITABLE = ['new', 'triage', 'applicability', 'returned']

route('GET', '/api/perimeter/changes/:id', (ctx) => {
  const user = requirePerm(ctx, 'perimeter:read')
  const c = findRow('changes', CHANGES, ctx.params[0]!, 'Regulatory change')
  const pick = (resource: string, ids: number[], fields: string[]) =>
    ids.map(id => current(resource, id)).filter(Boolean).map(r => Object.fromEntries(fields.map(f => [f, r![f]])))
  const source = SOURCES.find(s => s.id === c.sourceId) ?? null
  return {
    ...changeSummary(c),
    summary: c.summary,
    source: source && { ...source, pack: JURISDICTION_PACKS.find(p => p.code === source.jurisdiction)?.name ?? source.jurisdiction },
    extracted: c.extracted,
    applicability: c.applicability.map((a: Row) => ({ ...a, entityName: entityName(a.entityId), jurisdiction: LEGAL_ENTITIES.find(e => e.id === a.entityId)?.jurisdiction })),
    impacts: {
      obligations: pick('obligations', c.impacts.obligationIds, ['id', 'ref', 'title', 'status']),
      controls: pick('controls', c.impacts.controlIds, ['id', 'ref', 'name', 'effectiveness']),
      policies: pick('policies', c.impacts.policyIds, ['id', 'ref', 'title', 'status']),
      risks: pick('risks', c.impacts.riskIds, ['id', 'ref', 'title']),
    },
    impactIds: c.impacts,
    actions: c.actions.map((a: Row) => ({ ...a, ownerName: userName(a.ownerId) })),
    history: [...c.history, ...eventsOf(`changes:${c.id}`)],
    editable: EDITABLE.includes(c.status) && can(user.role, 'perimeter:write'),
    canApprove: c.status === 'pending_approval' && can(user.role, 'perimeter:approve') && c.makerId !== user.id,
    isMaker: c.makerId === user.id,
  }
})

route('POST', '/api/perimeter/changes/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const checker = action === 'approve' || action === 'return'
  const user = requirePerm(ctx, checker ? 'perimeter:approve' : 'perimeter:write')
  const c = findRow('changes', CHANGES, ctx.params[0]!, 'Regulatory change')
  const b = ctx.body
  if (!checker && !EDITABLE.includes(c.status)) {
    fail(409, c.status === 'pending_approval' ? 'This assessment is waiting for approval. It can be changed only if the approver returns it.' : 'This change is approved and closed.')
  }
  const history = (note: string, text: string | null = null) => addEvent(`changes:${c.id}`, { at: nowIso(), actorName: user.name, action: note, note: text })
  switch (action) {
    case 'validate': {
      const item = c.extracted.find((x: Row) => x.id === b.extractId) ?? fail(404, 'Requirement not found')
      const text = b.text === undefined ? item.text : minLength(b.text, 10, 'Requirement text must be at least 10 characters')
      patch('changes', c.id, { extracted: c.extracted.map((x: Row) => (x.id === item.id ? { ...x, text, validated: true } : x)), status: c.status === 'new' ? 'triage' : c.status })
      history('Extracted requirement validated', text)
      break
    }
    case 'triage':
      if (!['high', 'medium', 'low'].includes(b.priority)) fail(400, 'Choose a priority')
      patch('changes', c.id, { priority: b.priority, status: 'applicability', makerId: user.id })
      history(`Triaged as ${b.priority} priority`)
      break
    case 'applicability': {
      if (!['applicable', 'partially_applicable', 'not_applicable'].includes(b.decision)) fail(400, 'Choose an applicability decision')
      const rationale = minLength(b.rationale, 5, 'Give a rationale for the decision (at least 5 characters)')
      patch('changes', c.id, {
        applicability: c.applicability.map((a: Row) => (a.entityId === b.entityId ? { ...a, decision: b.decision, rationale } : a)),
        status: c.status === 'new' || c.status === 'triage' ? 'applicability' : c.status, makerId: user.id,
      })
      history(`Applicability for ${entityName(b.entityId)}: ${b.decision.replace(/_/g, ' ')}`, rationale)
      break
    }
    case 'map': {
      const ids = (v: unknown) => (Array.isArray(v) ? v.map(Number).filter(Number.isInteger) : [])
      patch('changes', c.id, { impacts: { obligationIds: ids(b.obligationIds), controlIds: ids(b.controlIds), policyIds: ids(b.policyIds), riskIds: ids(b.riskIds) }, makerId: user.id })
      history('Impact mapping updated')
      break
    }
    case 'add-action': {
      const title = minLength(b.title, 5, 'Describe the action (at least 5 characters)')
      if (!b.ownerId || !b.dueAt) fail(400, 'Choose an owner and a due date')
      patch('changes', c.id, { actions: [...c.actions, { id: nextId(), title, ownerId: b.ownerId, dueAt: toIso(b.dueAt), status: 'open' }] })
      notifyUser(b.ownerId, { severity: 'info', title: `Regulatory change action: ${c.ref}`, body: title, link: `/perimeter/changes?id=${c.id}` })
      history('Action assigned', `${title} (${userName(b.ownerId)})`)
      break
    }
    case 'submit': {
      const pending = c.extracted.filter((x: Row) => !x.validated).length
      if (pending) fail(409, `${pending} extracted ${pending === 1 ? 'requirement needs' : 'requirements need'} human validation before submission`)
      const missing = c.applicability.filter((a: Row) => !a.decision).map((a: Row) => entityName(a.entityId))
      if (missing.length) fail(409, `Record applicability for: ${missing.join(', ')}`)
      const applies = c.applicability.some((a: Row) => a.decision !== 'not_applicable')
      const mapped = Object.values(c.impacts as Record<string, number[]>).some(list => list.length)
      if (applies && !mapped) fail(409, 'Map the impacted obligations, controls, policies or risks before submitting')
      patch('changes', c.id, { status: 'pending_approval', makerId: user.id })
      notifyRole('CCO', { severity: 'info', title: `Regulatory change ready for approval: ${c.ref}`, body: c.title, link: `/perimeter/changes?id=${c.id}` })
      history('Submitted for approval')
      break
    }
    case 'approve':
      if (c.status !== 'pending_approval') fail(409, 'Only submitted assessments can be approved')
      if (c.makerId === user.id) fail(403, 'Maker-checker: you prepared this assessment, so a different person must approve it')
      patch('changes', c.id, { status: 'approved', approverId: user.id })
      notifyUser(c.makerId, { severity: 'info', title: `Regulatory change approved: ${c.ref}`, link: `/perimeter/changes?id=${c.id}` })
      history('Approved', b.note || null)
      break
    case 'return':
      if (c.status !== 'pending_approval') fail(409, 'Only submitted assessments can be returned')
      patch('changes', c.id, { status: 'returned' })
      notifyUser(c.makerId, { severity: 'info', title: `Regulatory change returned: ${c.ref}`, body: b.note, link: `/perimeter/changes?id=${c.id}` })
      history('Returned to preparer', minLength(b.note, 3, 'Explain what needs to change'))
      break
    default:
      fail(404, 'Unknown action')
  }
  await audit(user, `regulatory_change.${action.replace('-', '_')}`, 'regulatory_change', c.id, { ref: c.ref })
  return changeSummary(findRow('changes', CHANGES, c.id, 'Regulatory change'))
})

// ── Unified traceability chain ─────────────────────────────────────────────────────────────

route('GET', '/api/trace/obligation/:id', (ctx) => {
  requirePerm(ctx, 'compliance:read')
  const id = Number(ctx.params[0])
  const ob = current('obligations', id) ?? fail(404, 'Obligation not found')
  const source = SOURCES.find(s => s.id === OBLIGATION_SOURCE[id]) ?? null
  const changes = rowsOf('changes', CHANGES).filter(c => c.impacts.obligationIds.includes(id))
  const entityIds = new Set<string>(['E1', ...changes.flatMap(c => c.applicability.filter((a: Row) => a.decision && a.decision !== 'not_applicable').map((a: Row) => a.entityId))])
  const controls = (ob.controlIds as number[]).map(cid => current('controls', cid)).filter(Boolean) as Row[]
  const controlIds = controls.map(c => c.id)
  const risks = allRisks().filter(r => r.controls.some((c: Row) => controlIds.includes(c.id)))
  const ruleIds = [...new Set(controls.map(c => c.ccmRuleId).filter(Boolean))] as string[]
  const rules = ruleIds.map(rid => current('rules', rid)).filter(Boolean) as Row[]
  const alerts = allAlerts().filter(a => open(a.status) && (ruleIds.includes(a.ruleId) || (a.entityType === 'control' && controlIds.includes(a.entityId))))
  const actions = changes.flatMap(c => c.actions.map((a: Row) => ({ ...a, ownerName: userName(a.ownerId), source: c.ref })))
  const activities = rowsOf('activities', capturedAny<Row>('/api/assurance')?.activities ?? []).filter(a => a.controlIds.some((cid: number) => controlIds.includes(cid)))
  const evidence = (ob.evidenceCount ?? 0) + evidenceFor('obligation', id).length + controlIds.reduce((n, cid) => n + evidenceFor('control', cid).length, 0)

  const reasons: string[] = []
  if (!controls.length) reasons.push('No controls are mapped to this obligation.')
  controls.filter(c => c.effectiveness === 'ineffective').forEach(c => reasons.push(`${c.ref} is ineffective in testing.`))
  controls.filter(c => c.effectiveness === 'partially_effective' || c.effectiveness === 'not_tested').forEach(c => reasons.push(`${c.ref} is ${c.effectiveness.replace('_', ' ')}.`))
  rules.filter(r => r.state === 'fail').forEach(r => reasons.push(`Monitoring rule ${r.id} is failing.`))
  if (alerts.length) reasons.push(`${alerts.length} open ${alerts.length === 1 ? 'alert' : 'alerts'} on the mapped controls.`)
  const conclusion = !controls.length || controls.some(c => c.effectiveness === 'ineffective') ? 'not_assured' : reasons.length ? 'partially_assured' : 'assured'

  return {
    source: source && { id: source.id, ref: source.ref, title: source.title, issuer: source.issuer, url: source.url, version: source.version, checkedAt: source.checkedAt },
    obligation: { id: ob.id, ref: ob.ref, title: ob.title, status: ob.status },
    entities: [...entityIds].map(e => ({ id: e, name: entityName(e) })),
    changes: changes.map(c => ({ id: c.id, ref: c.ref, title: c.title, status: c.status })),
    risks: risks.map(r => ({ id: r.id, ref: r.ref, title: r.title, band: r.residual.band })),
    controls: controls.map(c => ({ id: c.id, ref: c.ref, name: c.name, effectiveness: c.effectiveness })),
    rules: rules.map(r => ({ id: r.id, name: r.name, state: r.state })),
    alerts: alerts.map(a => ({ id: a.id, ref: a.ref, title: a.title, severity: a.severity })),
    actions,
    evidence,
    assurance: activities.map(a => ({ id: a.id, title: a.title, line: a.line, status: a.status, rating: a.rating })),
    conclusion, reasons,
  }
})

// ── Transaction monitoring ─────────────────────────────────────────────────────────────────

const channelName = (key: string) => TM_CHANNELS.find(c => c.key === key)?.name ?? key
const scenarioOf = (id: string) => rowsOf('tm_scenarios', TM_SCENARIOS).find(s => s.id === id)

function tmAlertView(user: DemoUser, a: Row) {
  const s = scenarioOf(a.scenarioId)
  return {
    ...a, customer: customerOf(user, a.customerId), entityName: entityName(a.entityId), channelName: channelName(a.channel),
    scenarioName: s?.name ?? a.scenarioId, assigneeName: userName(a.assigneeId),
    caseId: a.caseRef ? casesNow().find(c => c.ref === a.caseRef)?.id ?? null : null,
  }
}

route('GET', '/api/tm/overview', (ctx) => {
  const user = requirePerm(ctx, 'tm:read')
  const alerts = rowsOf('tm_alerts', TM_ALERTS).sort((a, b) => Date.parse(b.at) - Date.parse(a.at))
  const scenarios = rowsOf('tm_scenarios', TM_SCENARIOS)
  const live = scenarios.filter(s => s.status === 'active')
  const totalAlerts = live.reduce((n, s) => n + s.alerts30d, 0)
  return {
    kpis: {
      open: alerts.filter(a => a.status === 'new' || a.status === 'in_review').length,
      unassigned: alerts.filter(a => a.status === 'new' && !a.assigneeId).length,
      highScore: alerts.filter(a => (a.status === 'new' || a.status === 'in_review') && a.score >= 75).length,
      escalated: alerts.filter(a => a.status === 'escalated').length,
      falsePositiveRate: Math.round(live.reduce((n, s) => n + s.falsePositiveRate * s.alerts30d, 0) / Math.max(totalAlerts, 1)),
      transactions24h: TM_CHANNELS.reduce((n, c) => n + c.volume24h, 0),
      scenariosActive: live.length,
      scenariosTesting: scenarios.length - live.length,
    },
    channels: TM_CHANNELS.map(c => ({ ...c, entities: c.entityIds.map(entityName) })),
    scenarios: scenarios.map(s => ({ ...s, ownerName: userName(s.ownerId), channelNames: s.channels.map(channelName) })),
    alerts: alerts.map(a => tmAlertView(user, a)),
  }
})

route('GET', '/api/tm/alerts/:id', (ctx) => {
  const user = requirePerm(ctx, 'tm:read')
  const a = findRow('tm_alerts', TM_ALERTS, ctx.params[0]!, 'Monitoring alert')
  const s = scenarioOf(a.scenarioId)
  return { ...tmAlertView(user, a), scenario: s && { id: s.id, name: s.name, threshold: s.threshold, kind: s.kind }, history: eventsOf(`tm_alerts:${a.id}`) }
})

route('POST', '/api/tm/alerts/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const user = requirePerm(ctx, 'tm:write')
  const a = findRow('tm_alerts', TM_ALERTS, ctx.params[0]!, 'Monitoring alert')
  if (a.status === 'closed' || a.status === 'escalated') fail(409, `Alert is already ${a.status}`)
  const b = ctx.body
  let result: Row = {}
  switch (action) {
    case 'assign':
      patch('tm_alerts', a.id, { assigneeId: b.assigneeId ?? user.id, status: 'in_review' })
      addEvent(`tm_alerts:${a.id}`, { at: nowIso(), actorName: user.name, action: `Assigned to ${userName(b.assigneeId ?? user.id)}`, note: null })
      break
    case 'close': {
      const rationale = minLength(b.rationale, 10, 'Record why no further action is needed (at least 10 characters)')
      patch('tm_alerts', a.id, { status: 'closed', outcome: `${b.falsePositive ? 'False positive: ' : 'No further action: '}${rationale}`, assigneeId: a.assigneeId ?? user.id })
      addEvent(`tm_alerts:${a.id}`, { at: nowIso(), actorName: user.name, action: 'Closed', note: rationale })
      break
    }
    case 'escalate': {
      const note = minLength(b.note, 5, 'Explain the reason for escalation (at least 5 characters)')
      const customer = current('customers', a.customerId) ?? fail(404, 'Customer not found')
      // Transparent score: customer factors plus the scenario, adding up to the alert score.
      const base = scoreAml({ ...customer, amount: a.amountGbp })
      const scenario = scenarioOf(a.scenarioId)
      const extra = Math.max(0, a.score - base.score)
      const factors = [...(extra ? [{ factor: `Monitoring scenario ${a.scenarioId}: ${scenario?.name ?? ''}`.trim(), weight: extra }] : []), ...base.factors]
      const made = openCase({
        customerId: a.customerId, type: a.channel === 'crypto' ? 'crypto' : 'transaction', score: Math.min(100, Math.max(a.score, base.score)),
        scoreFactors: factors.sort((x, y) => y.weight - x.weight), status: 'under_review', analystId: user.id,
      })
      patch('tm_alerts', a.id, { status: 'escalated', caseRef: made.ref, outcome: `Escalated to case ${made.ref}`, assigneeId: a.assigneeId ?? user.id })
      addEvent(`tm_alerts:${a.id}`, { at: nowIso(), actorName: user.name, action: `Escalated to case ${made.ref}`, note })
      notifyRole('MLRO', { severity: 'high', title: `Monitoring alert escalated: ${a.ref}`, body: `Case ${made.ref} opened`, link: `/cases?case=${made.id}` })
      result = { caseRef: made.ref, caseId: made.id }
      break
    }
    default:
      fail(404, 'Unknown action')
  }
  await audit(user, `tm_alert.${action}`, 'tm_alert', a.id, { ref: a.ref, ...result })
  return { ...tmAlertView(user, findRow('tm_alerts', TM_ALERTS, a.id, 'Monitoring alert')), ...result }
})

route('PATCH', '/api/tm/scenarios/:id', async (ctx) => {
  const user = requirePerm(ctx, 'tm:write')
  const s = scenarioOf(ctx.params[0]!) ?? fail(404, 'Scenario not found')
  const reason = minLength(ctx.body.reason, 5, 'Give a reason for the change (recorded in the audit log)')
  const next: Row = { lastTunedAt: nowIso() }
  if (ctx.body.threshold !== undefined) next.threshold = minLength(ctx.body.threshold, 5, 'Describe the threshold')
  if (ctx.body.status !== undefined) {
    if (!['active', 'testing'].includes(ctx.body.status)) fail(400, 'Unknown status')
    next.status = ctx.body.status
  }
  patch('tm_scenarios', s.id, next)
  await audit(user, 'tm_scenario.tuned', 'tm_scenario', s.id, { reason, from: { threshold: s.threshold, status: s.status }, to: next })
  return scenarioOf(s.id)
})

// ── Digital Assets Intelligence ────────────────────────────────────────────────────────────

const walletsNow = () => rowsOf('wallets', WALLETS)

route('GET', '/api/crypto/overview', (ctx) => {
  const user = requirePerm(ctx, 'crypto:read')
  const wallets = walletsNow().map(w => ({ ...w, customer: customerOf(user, w.customerId) }))
  const transfers = rowsOf('transfers', TRANSFERS).map((t) => {
    const cw = t.counterpartyWalletId ? walletsNow().find(w => w.id === t.counterpartyWalletId) : null
    return { ...t, customer: customerOf(user, t.customerId), entityName: entityName(t.entityId), counterpartyWallet: cw && { id: cw.id, address: cw.address, label: cw.label, riskScore: cw.riskScore }, caseId: t.caseRef ? casesNow().find(c => c.ref === t.caseRef)?.id ?? null : null }
  })
  const travelRule = rowsOf('travel_rule', TRAVEL_RULE)
  const categories = new Map<string, { wallets: number, maxPercent: number }>()
  for (const w of wallets) {
    for (const e of w.exposures) {
      if (e.category === 'exchange') continue
      const cur = categories.get(e.category) ?? { wallets: 0, maxPercent: 0 }
      categories.set(e.category, { wallets: cur.wallets + 1, maxPercent: Math.max(cur.maxPercent, e.percent) })
    }
  }
  return {
    kpis: {
      walletsScreened: wallets.length,
      highRisk: wallets.filter(w => w.riskScore >= 75).length,
      blocked: wallets.filter(w => w.status === 'blocked').length,
      transfersOpen: transfers.filter(t => t.status === 'open').length,
      travelRuleExceptions: travelRule.filter(t => t.status === 'exception' || t.status === 'pending_info').length,
      crossChain: transfers.filter(t => t.crossChain).length,
    },
    wallets, transfers, travelRule,
    exposure: [...categories].map(([category, v]) => ({ category, ...v })).sort((a, b) => b.wallets - a.wallets || b.maxPercent - a.maxPercent),
  }
})

const CHAINS = ['Bitcoin', 'Ethereum', 'Tron', 'Polygon', 'Solana']

route('POST', '/api/crypto/wallets/screen', async (ctx) => {
  const user = requirePerm(ctx, 'crypto:write')
  const address = String(ctx.body.address ?? '').trim()
  if (address.length < 20) fail(400, 'Enter a full wallet address')
  if (!CHAINS.includes(ctx.body.chain)) fail(400, 'Choose a blockchain')
  const existing = walletsNow().find(w => w.address === address)
  if (existing) return existing
  // No analytics provider is contracted yet, so the result is a deterministic illustration.
  const h = demoHash(address)
  const riskScore = Number.parseInt(h.slice(0, 4), 16) % 100
  const risky = (['sanctioned', 'mixer', 'darknet', 'scam', 'stolen_funds', 'high_risk_service'] as const)[Number.parseInt(h.slice(4, 6), 16) % 6]!
  const exposures = riskScore >= 50
    ? [{ category: risky, percent: Math.max(5, Math.round(riskScore / 2)), kind: riskScore >= 85 ? 'direct' : 'indirect' }, { category: 'exchange', percent: 100 - Math.max(5, Math.round(riskScore / 2)), kind: 'direct' }]
    : [{ category: 'exchange', percent: 100, kind: 'direct' }]
  const wallet = create('wallets', {
    id: nextId(), address, chain: ctx.body.chain, asset: ctx.body.chain === 'Bitcoin' ? 'BTC' : ctx.body.chain === 'Tron' ? 'USDT' : 'Multiple',
    customerId: null, label: 'Screened on request', riskScore, status: riskScore >= 85 ? 'blocked' : riskScore >= 50 ? 'review' : 'clear',
    exposures, screenedAt: nowIso(), illustrative: true,
  })
  await audit(user, 'wallet.screened', 'wallet', wallet.id, { address, chain: wallet.chain, riskScore, status: wallet.status })
  return wallet
})

route('POST', '/api/crypto/transfers/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const user = requirePerm(ctx, 'crypto:write')
  const t = findRow('transfers', TRANSFERS, ctx.params[0]!, 'Transfer')
  if (t.status !== 'open') fail(409, `Transfer is already ${t.status}`)
  const note = minLength(ctx.body.note, 5, 'Record your rationale (at least 5 characters)')
  let result: Row = {}
  if (action === 'escalate') {
    const cw = t.counterpartyWalletId ? walletsNow().find(w => w.id === t.counterpartyWalletId) : null
    const risky = (cw?.exposures ?? []).filter((e: Row) => e.category !== 'exchange')
    const total = risky.reduce((n: number, e: Row) => n + e.percent, 0) || 1
    const factors = risky.map((e: Row) => ({ factor: `${e.kind === 'direct' ? 'Direct' : 'Indirect'} exposure: ${e.category.replace('_', ' ')} (${e.percent}%)`, weight: Math.round(t.riskScore * e.percent / total) }))
    const made = openCase({ customerId: t.customerId, type: 'crypto', score: t.riskScore, scoreFactors: factors.length ? factors : [{ factor: 'Blockchain exposure', weight: t.riskScore }], status: 'under_review', analystId: user.id })
    patch('transfers', t.id, { status: 'escalated', caseRef: made.ref })
    notifyRole('MLRO', { severity: 'high', title: `Crypto transfer escalated: ${t.asset} ${t.amount}`, body: `Case ${made.ref} opened`, link: `/cases?case=${made.id}` })
    result = { caseRef: made.ref, caseId: made.id }
  }
  else if (action === 'clear') patch('transfers', t.id, { status: 'cleared' })
  else fail(404, 'Unknown action')
  await audit(user, `crypto_transfer.${action}`, 'crypto_transfer', t.id, { hash: t.hash.slice(0, 16), note, ...result })
  return { ...findRow('transfers', TRANSFERS, t.id, 'Transfer'), ...result }
})

route('POST', '/api/crypto/travel-rule/:id/:action', async (ctx) => {
  const action = ctx.params[1]!
  const user = requirePerm(ctx, 'crypto:write')
  const item = findRow('travel_rule', TRAVEL_RULE, ctx.params[0]!, 'Travel Rule record')
  if (item.status === 'resolved' || item.status === 'rejected') fail(409, `Already ${item.status}`)
  const note = minLength(ctx.body.note, 5, 'Add a note (at least 5 characters)')
  switch (action) {
    case 'request-info':
      patch('travel_rule', item.id, { status: 'pending_info', note })
      break
    case 'complete-vasp-dd':
      if (item.vaspDueDiligence !== 'pending' && item.vaspDueDiligence !== 'not_started') fail(409, 'No counterparty due diligence is outstanding')
      patch('travel_rule', item.id, { vaspDueDiligence: 'complete', note })
      break
    case 'resolve':
      if (item.vaspDueDiligence === 'pending' || item.vaspDueDiligence === 'not_started') fail(409, 'Complete due diligence on the counterparty VASP before releasing the transfer')
      patch('travel_rule', item.id, { status: 'resolved', originatorComplete: true, beneficiaryComplete: true, note })
      break
    case 'reject':
      patch('travel_rule', item.id, { status: 'rejected', note })
      break
    default:
      fail(404, 'Unknown action')
  }
  await audit(user, `travel_rule.${action.replace(/-/g, '_')}`, 'travel_rule', item.id, { asset: item.asset, note })
  return findRow('travel_rule', TRAVEL_RULE, item.id, 'Travel Rule record')
})

// ── Delivery gates (internal programme view) ───────────────────────────────────────────────

route('GET', '/api/programme/gates', (ctx) => {
  requirePerm(ctx, 'programme:read')
  return GATES.map(g => applyPatch('gates', structuredClone({ ...g, decidedAt: null as string | null, decidedByName: null as string | null, note: null as string | null })))
})

route('POST', '/api/programme/gates/:id/decision', async (ctx) => {
  const user = requirePerm(ctx, 'programme:write')
  const id = Number(ctx.params[0])
  const gate = GATES.find(g => g.id === id) ?? fail(404, 'Gate not found')
  const status = gateStatus(id)
  if (status === 'approved') fail(409, 'This gate is already approved')
  if (id > 0 && gateStatus(id - 1) !== 'approved') fail(409, `Gate ${id - 1} must be approved first`)
  const note = minLength(ctx.body.note, 5, 'Record the decision rationale (at least 5 characters)')
  const decision = ctx.body.decision === 'approved' ? 'approved' : 'deferred'
  patch('gates', id, { status: decision, decidedAt: nowIso(), decidedByName: user.name, note })
  if (decision === 'approved' && id < GATES.length - 1 && gateStatus(id + 1) === 'not_started') patch('gates', id + 1, { status: 'pending_decision' })
  await audit(user, `programme.gate_${decision}`, 'programme_gate', id, { gate: gate.name, note })
  return { ok: true }
})

// ── Search ─────────────────────────────────────────────────────────────────────────────────

searchProviders.push((user, hit) => {
  const out: { domain: string, ref: string, title: string, to: string }[] = []
  const allowed = (p: Permission) => can(user.role, p)
  if (allowed('perimeter:read')) {
    rowsOf('changes', CHANGES).filter(c => hit(c.title, c.ref, c.issuer)).slice(0, 4)
      .forEach(c => out.push({ domain: 'Regulatory change', ref: c.ref, title: c.title, to: `/perimeter/changes?id=${c.id}` }))
    SOURCES.filter(s => hit(s.title, s.ref)).slice(0, 3)
      .forEach(s => out.push({ domain: 'Regulatory source', ref: s.ref, title: s.title, to: '/perimeter?tab=sources' }))
  }
  if (allowed('tm:read')) {
    rowsOf('tm_alerts', TM_ALERTS).filter(a => hit(a.ref, a.summary)).slice(0, 3)
      .forEach(a => out.push({ domain: 'Monitoring alert', ref: a.ref, title: a.summary, to: `/transaction-monitoring?id=${a.id}` }))
  }
  if (allowed('crypto:read')) {
    walletsNow().filter(w => hit(w.address, w.label)).slice(0, 3)
      .forEach(w => out.push({ domain: 'Wallet', ref: `${w.address.slice(0, 10)}…`, title: w.label, to: '/digital-assets' }))
  }
  return out
})
