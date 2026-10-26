import { eq, inArray } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ ids: z.array(z.number().int()).min(1).max(200), assigneeId: z.number().int() })

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'alerts:act')
  const { ids, assigneeId } = await validateBody(event, body)
  const name = userNames([assigneeId]).get(assigneeId)
  if (!name) throw createError({ statusCode: 400, statusMessage: 'Unknown user' })

  const { alerts } = tables
  const db = useDb()
  const targets = db.select().from(alerts).where(whereAll(inArray(alerts.id, ids), buScope(user, alerts.businessUnit))).all()
    .filter(a => a.status !== 'closed')
  for (const a of targets) {
    const updated = db.update(alerts).set({ assigneeId }).where(eq(alerts.id, a.id)).returning().get()
    addAlertEvent(a.id, actorOf(user), 'assigned', `Bulk-assigned to ${name}`)
    emitAlertUpdate(updated)
  }
  if (targets.length) notifyUser(assigneeId, { severity: 'info', title: `${targets.length} alert(s) assigned to you`, link: '/alerts' })
  writeAudit(actorOf(user), 'alert.bulk_assigned', 'alert', null, { refs: targets.map(a => a.ref), assignee: name }, event)
  return { updated: targets.length }
})
