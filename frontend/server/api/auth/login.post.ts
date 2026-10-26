import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ email: z.string().email().toLowerCase(), password: z.string().min(1) })

export default defineEventHandler(async (event) => {
  const { email, password } = await validateBody(event, body)
  const user = useDb().select().from(tables.users).where(eq(tables.users.email, email)).get()

  // Same message for unknown user and wrong password, no account enumeration.
  const invalid = () => createError({ statusCode: 401, statusMessage: 'Invalid email or password' })
  if (!user) {
    await hashPassword(password) // equalise timing
    throw invalid()
  }
  assertNotLocked(user)
  if (!(await verifyPassword(user.passwordHash, password))) {
    recordFailure(user, event, 'password')
    throw invalid()
  }

  const { mfaEnforced } = useRuntimeConfig()
  if (!user.mfaEnabled && !mfaEnforced) return completeLogin(event, user)

  // Second factor required (§2.3 "MFA enforced for all users").
  await replaceUserSession(event, {
    pendingMfa: { userId: user.id, enroll: !user.mfaEnabled },
    secure: user.mfaEnabled ? {} : { enrollSecret: generateTotpSecret() },
  })
  return { next: user.mfaEnabled ? '/login/mfa' : '/login/enroll' }
})
