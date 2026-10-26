import { and, eq, inArray, isNull } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ ids: z.array(z.number().int()).optional() })

export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const { ids } = await validateBody(event, body)
  const { notifications } = tables
  useDb().update(notifications).set({ readAt: new Date() })
    .where(and(eq(notifications.userId, user.id), isNull(notifications.readAt), ids?.length ? inArray(notifications.id, ids) : undefined)).run()
  return { ok: true }
})
