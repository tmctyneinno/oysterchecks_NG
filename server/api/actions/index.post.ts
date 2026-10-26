import { z } from 'zod'

const body = z.object({
  title: z.string().trim().min(5).max(300),
  sourceType: z.enum(['incident', 'finding', 'alert', 'risk']),
  sourceId: z.number().int(),
  ownerId: z.number().int(),
  dueAt: z.coerce.date(),
})

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'incidents:write')
  const input = await validateBody(event, body)
  const { actions } = tables
  const row = useDb().insert(actions).values({ ...input, ref: nextRef(actions, actions.ref, 'ACT', 4), status: 'open' }).returning().get()
  notifyUser(input.ownerId, { severity: 'info', title: `Corrective action assigned: ${row.ref}`, body: row.title, link: '/incidents?tab=actions' })
  writeAudit(actorOf(user), 'action.created', 'action', row.id, { ref: row.ref, source: `${input.sourceType}:${input.sourceId}` }, event)
  return row
})
