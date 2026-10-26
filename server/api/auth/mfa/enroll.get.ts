import QRCode from 'qrcode'

export default defineEventHandler(async (event) => {
  const { user, session, enroll } = await pendingUser(event)
  const secret = session.secure?.enrollSecret
  if (!enroll || !secret) throw createError({ statusCode: 400, statusMessage: 'MFA is already set up for this account' })

  const otpauthUrl = `otpauth://totp/${encodeURIComponent(`OysterChecks:${user.email}`)}?secret=${secret}&issuer=OysterChecks&algorithm=SHA1&digits=6&period=30`
  return {
    email: user.email,
    secret: secret.match(/.{1,4}/g)!.join(' '),
    qr: await QRCode.toDataURL(otpauthUrl, { margin: 1, width: 220, color: { dark: '#070E1C', light: '#FFFFFF' } }),
  }
})
