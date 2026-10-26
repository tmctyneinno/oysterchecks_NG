import { and, eq } from 'drizzle-orm'

interface NotificationInput { severity: Severity | 'info', title: string, body?: string, link?: string }

export function notifyUser(userId: number, n: NotificationInput) {
  useDb().insert(tables.notifications).values({ userId, at: new Date(), ...n }).run()
  publish({ type: 'notification', payload: { userId, title: n.title, severity: n.severity } })
  // §4.4 "notification sent via platform + email/SMS". External channels are not wired yet:
  // this is the single seam where an email/SMS provider plugs in.
  if (n.severity === 'critical') console.info(`[notify:email+sms] user=${userId} ${n.title}`)
}

export function usersWithRole(role: RoleKey, businessUnit?: string | null) {
  const { users } = tables
  return useDb().select({ id: users.id, name: users.name }).from(users)
    .where(and(eq(users.role, role), eq(users.active, true), businessUnit ? eq(users.businessUnit, businessUnit) : undefined))
    .all()
}

export function notifyRole(role: RoleKey, n: NotificationInput, businessUnit?: string | null) {
  const recipients = usersWithRole(role, businessUnit)
  for (const u of recipients) notifyUser(u.id, n)
  return recipients
}
