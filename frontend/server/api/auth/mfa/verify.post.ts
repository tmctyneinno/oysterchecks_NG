import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({ code: z.string().regex(/^\d{6}$/, 'Enter the 6-digit code') })

export default defineEventHandler(async (event) => {
  const { code } = await validateBody(event, body)
  const { user, session, enroll } = await pendingUser(event)

  const secret = enroll ? session.secure?.enrollSecret : user.mfaSecret ? decryptField(user.mfaSecret) : null
  if (!secret) throw createError({ statusCode: 400, statusMessage: 'Sign-in session expired. Start again.' })

  if (!verifyTotp(secret, code)) {
    recordFailure(user, event, 'mfa')
    throw createError({ statusCode: 401, statusMessage: 'Incorrect code. Check your authenticator app and try again.' })
  }

  if (enroll) {
    useDb().update(tables.users).set({ mfaSecret: encryptField(secret), mfaEnabled: true }).where(eq(tables.users.id, user.id)).run()
    writeAudit({ id: user.id, name: user.name }, 'auth.mfa_enrolled', 'user', user.id, {}, event)
  }
  return completeLogin(event, { ...user, mfaEnabled: true })
})
