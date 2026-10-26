import type { H3Event } from 'h3'
import { eq } from 'drizzle-orm'

const MAX_ATTEMPTS = 5
const LOCK_MINUTES = 15

type UserRow = typeof tables.users.$inferSelect

export function assertNotLocked(user: UserRow) {
  if (!user.active) throw createError({ statusCode: 403, statusMessage: 'Account disabled' })
  if (user.lockedUntil && user.lockedUntil > new Date()) {
    throw createError({ statusCode: 423, statusMessage: `Account locked after repeated failures. Try again after ${user.lockedUntil.toLocaleTimeString('en-GB')}.` })
  }
}

export function recordFailure(user: UserRow, event: H3Event, stage: 'password' | 'mfa') {
  const failed = user.failedLogins + 1
  const lock = failed >= MAX_ATTEMPTS ? new Date(Date.now() + LOCK_MINUTES * 60_000) : null
  useDb().update(tables.users).set({ failedLogins: lock ? 0 : failed, lockedUntil: lock }).where(eq(tables.users.id, user.id)).run()
  writeAudit({ id: user.id, name: user.name }, lock ? 'auth.locked' : 'auth.failed', 'user', user.id, { stage }, event)
}

export async function completeLogin(event: H3Event, user: UserRow) {
  useDb().update(tables.users).set({ failedLogins: 0, lockedUntil: null, lastLoginAt: new Date() }).where(eq(tables.users.id, user.id)).run()
  await replaceUserSession(event, {
    user: { id: user.id, name: user.name, email: user.email, role: user.role, businessUnit: user.businessUnit },
    loggedInAt: Date.now(),
  })
  writeAudit({ id: user.id, name: user.name }, 'auth.login', 'user', user.id, { role: user.role }, event)
  return { next: ROLES[user.role].landing }
}

export async function pendingUser(event: H3Event) {
  const session = await getUserSession(event)
  if (!session.pendingMfa) throw createError({ statusCode: 401, statusMessage: 'Sign-in session expired. Start again.' })
  const user = useDb().select().from(tables.users).where(eq(tables.users.id, session.pendingMfa.userId)).get()
  if (!user) throw createError({ statusCode: 401, statusMessage: 'Sign-in session expired. Start again.' })
  assertNotLocked(user)
  return { user, session, enroll: session.pendingMfa.enroll }
}
