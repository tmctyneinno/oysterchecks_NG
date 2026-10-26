import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  severity: z.enum(['critical', 'high', 'medium', 'low']).optional(),
  type: z.enum(['operational', 'data', 'financial_crime', 'regulatory', 'audit_finding', 'cyber']).optional(),
  status: z.enum(['open', 'investigating', 'remediating']).optional(),
  ownerId: z.number().int().nullable().optional(),
  rcaMethod: z.enum(['5_whys', 'ishikawa']).nullable().optional(),
  rootCause: z.string().trim().max(4000).nullable().optional(),
  notifiable: z.boolean().optional(),
  notified: z.boolean().optional(),
  riskIds: z.array(z.number().int()).optional(),
  controlIds: z.array(z.number().int()).optional(),
  obligationIds: z.array(z.number().int()).optional(),
})

/** Triage, root-cause analysis, cross-module linking and regulatory notification (§4.8 steps 2–6). */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:write')
  const id = parseId(event)
  const { riskIds, controlIds, obligationIds, notified, ...patch } = await validateBody(event, body)
  const { incidents } = tables
  const db = useDb()
  const i = db.select().from(incidents).where(whereAll(eq(incidents.id, id), buScope(user, incidents.businessUnit))).get()
  if (!i) notFound('Incident')
  if (i.status === 'closed') throw createError({ statusCode: 409, statusMessage: 'Incident is closed' })

  const set: Partial<typeof i> = { ...patch }
  const type = patch.type ?? i.type
  if (patch.notifiable === true && !i.notifiable) {
    const rule = NOTIFICATION_RULES[type]!
    set.regulator = rule.regulator
    set.notificationDueAt = new Date(i.reportedAt.getTime() + rule.hours * 3_600_000)
    notifyRole('CCO', { severity: 'high', title: `Regulatory notification required: ${i.ref} → ${rule.regulator}`, body: rule.basis, link: `/incidents?id=${id}` })
  }
  if (patch.notifiable === false) Object.assign(set, { regulator: null, notificationDueAt: null })
  if (notified) set.notifiedAt = new Date()

  const updated = db.update(incidents).set(set).where(eq(incidents.id, id)).returning().get()
  if (riskIds) setLinks('incident', id, 'risk', riskIds)
  if (controlIds) setLinks('incident', id, 'control', controlIds)
  if (obligationIds) setLinks('incident', id, 'obligation', obligationIds)
  writeAudit(actorOf(user), notified ? 'incident.regulator_notified' : 'incident.updated', 'incident', id, { ref: i.ref, fields: Object.keys(set), linked: { risks: riskIds?.length, controls: controlIds?.length } }, event)
  return updated
})
