import { and, eq, inArray } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:read')
  const id = parseId(event)
  const db = useDb()
  const t = tables
  const i = db.select().from(t.incidents).where(whereAll(eq(t.incidents.id, id), buScope(user, t.incidents.businessUnit))).get()
  if (!i) notFound('Incident')
  const riskIds = linkedIds('incident', id, 'risk')
  const controlIds = linkedIds('incident', id, 'control')
  const obligationIds = linkedIds('incident', id, 'obligation')
  const acts = db.select().from(t.actions).where(and(eq(t.actions.sourceType, 'incident'), eq(t.actions.sourceId, id))).all()
  const names = userNames([i.ownerId, i.reportedById, i.closedById, ...acts.map(a => a.ownerId)])
  return {
    ...i,
    ownerName: i.ownerId ? names.get(i.ownerId) ?? null : null,
    reportedByName: names.get(i.reportedById) ?? 'Unknown',
    closedByName: i.closedById ? names.get(i.closedById) ?? null : null,
    risks: riskIds.length ? db.select({ id: t.risks.id, ref: t.risks.ref, title: t.risks.title }).from(t.risks).where(inArray(t.risks.id, riskIds)).all() : [],
    controls: controlIds.length ? db.select({ id: t.controls.id, ref: t.controls.ref, name: t.controls.name }).from(t.controls).where(inArray(t.controls.id, controlIds)).all() : [],
    obligations: obligationIds.length ? db.select({ id: t.obligations.id, ref: t.obligations.ref, title: t.obligations.title }).from(t.obligations).where(inArray(t.obligations.id, obligationIds)).all() : [],
    actions: acts.map(a => ({ ...a, ownerName: names.get(a.ownerId) ?? 'Unknown' })),
    evidence: listEvidence('incident', id),
    notificationRule: NOTIFICATION_RULES[i.type] ?? null,
  }
})
