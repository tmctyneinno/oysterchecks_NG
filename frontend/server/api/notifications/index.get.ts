import { and, desc, eq, isNull, sql } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const { notifications } = tables
  const db = useDb()
  const items = db.select().from(notifications).where(eq(notifications.userId, user.id)).orderBy(desc(notifications.id)).limit(40).all()
  const unread = db.select({ n: sql<number>`count(*)` }).from(notifications)
    .where(and(eq(notifications.userId, user.id), isNull(notifications.readAt))).get()?.n ?? 0
  return { items, unread }
})
