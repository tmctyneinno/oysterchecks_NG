import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  role: z.enum(ROLE_KEYS).optional(),
  businessUnit: z.string().nullable().optional(),
  active: z.boolean().optional(),
  unlock: z.boolean().optional(),
  resetMfa: z.boolean().optional(),
})

export default defineEventHandler(async (event) => {
  const admin = await requirePermission(event, 'users:manage')
  const id = parseId(event)
  const { unlock, resetMfa, ...patch } = await validateBody(event, body)
  if (id === admin.id && (patch.active === false || (patch.role && patch.role !== admin.role))) {
    throw createError({ statusCode: 409, statusMessage: 'You cannot deactivate or change the role of your own account' })
  }
  const { users } = tables
  const before = useDb().select().from(users).where(eq(users.id, id)).get()
  if (!before) notFound('User')
  useDb().update(users).set({
    ...patch,
    ...(unlock ? { lockedUntil: null, failedLogins: 0 } : {}),
    ...(resetMfa ? { mfaEnabled: false, mfaSecret: null } : {}),
  }).where(eq(users.id, id)).run()
  // Role changes take effect at the user's next sign-in; sessions are sealed cookies (8h max age).
  writeAudit(actorOf(admin), 'user.updated', 'user', id, { email: before.email, ...patch, unlock: !!unlock, resetMfa: !!resetMfa, previousRole: before.role }, event)
  return { ok: true }
})
