import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = incidentBody.extend({ sourceAlertId: z.number().int().optional() })

/** Any user with incidents:write can log an incident (§4.8 step 1), including from a CCM alert. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:write')
  const input = await validateBody(event, body)
  const businessUnit = ROLES[user.role].scope === 'bu' ? user.businessUnit! : input.businessUnit
  const db = useDb()
  const t = tables
  const row = db.insert(t.incidents).values({
    ...input, businessUnit, ref: nextRef(t.incidents, t.incidents.ref, 'INC', 4), status: 'open', reportedById: user.id, reportedAt: new Date(),
  }).returning().get()

  if (input.sourceAlertId) {
    const alert = db.select().from(t.alerts).where(eq(t.alerts.id, input.sourceAlertId)).get()
    if (alert) {
      addAlertEvent(alert.id, actorOf(user), 'incident_raised', `Incident ${row.ref} raised from this alert`)
      if (alert.entityType === 'control' && alert.entityId) setLinks('incident', row.id, 'control', [alert.entityId])
    }
  }
  if (input.severity === 'critical' || input.severity === 'high') {
    notifyRole('CRO', { severity: input.severity, title: `${input.severity.toUpperCase()} incident logged: ${row.ref}`, body: row.title, link: `/incidents?id=${row.id}` })
  }
  writeAudit(actorOf(user), 'incident.logged', 'incident', row.id, { ref: row.ref, severity: row.severity, type: row.type }, event)
  return row
})
