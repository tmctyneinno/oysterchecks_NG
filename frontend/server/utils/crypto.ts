import { createCipheriv, createDecipheriv, createHash, createHmac, randomBytes, timingSafeEqual } from 'node:crypto'

// ── Field encryption (AES-256-GCM) for secrets at rest, e.g. MFA seeds ─────

const key = () => createHash('sha256').update(`oc-field-key:${useRuntimeConfig().session.password}`).digest()

export function encryptField(plain: string) {
  const iv = randomBytes(12)
  const cipher = createCipheriv('aes-256-gcm', key(), iv)
  const data = Buffer.concat([cipher.update(plain, 'utf8'), cipher.final()])
  return [iv, cipher.getAuthTag(), data].map(b => b.toString('base64url')).join('.')
}

export function decryptField(payload: string) {
  const [iv, tag, data] = payload.split('.').map(p => Buffer.from(p, 'base64url'))
  const decipher = createDecipheriv('aes-256-gcm', key(), iv!)
  decipher.setAuthTag(tag!)
  return Buffer.concat([decipher.update(data!), decipher.final()]).toString('utf8')
}

// ── TOTP (RFC 6238, SHA-1, 30s, 6 digits), compatible with standard authenticator apps ──

const B32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'

export function base32Encode(buf: Buffer) {
  let bits = 0, value = 0, out = ''
  for (const byte of buf) {
    value = (value << 8) | byte
    bits += 8
    while (bits >= 5) { out += B32[(value >>> (bits - 5)) & 31]; bits -= 5 }
  }
  if (bits > 0) out += B32[(value << (5 - bits)) & 31]
  return out
}

export function base32Decode(str: string) {
  const clean = str.replace(/=+$/, '').toUpperCase()
  let bits = 0, value = 0
  const out: number[] = []
  for (const ch of clean) {
    const idx = B32.indexOf(ch)
    if (idx < 0) continue
    value = (value << 5) | idx
    bits += 5
    if (bits >= 8) { out.push((value >>> (bits - 8)) & 255); bits -= 8 }
  }
  return Buffer.from(out)
}

export const generateTotpSecret = () => base32Encode(randomBytes(20))

export function totpCode(secret: string, timeMs: number, step = 30) {
  const counter = Buffer.alloc(8)
  counter.writeBigUInt64BE(BigInt(Math.floor(timeMs / 1000 / step)))
  const hmac = createHmac('sha1', base32Decode(secret)).update(counter).digest()
  const offset = hmac[hmac.length - 1]! & 0xf
  const bin = (hmac.readUInt32BE(offset) & 0x7fffffff) % 1_000_000
  return bin.toString().padStart(6, '0')
}

/** Accepts the current code and one step either side to tolerate clock drift. */
export function verifyTotp(secret: string, code: string, now = Date.now()) {
  const candidate = Buffer.from(code.replace(/\s/g, ''))
  if (candidate.length !== 6) return false
  return [-1, 0, 1].some(w => timingSafeEqual(Buffer.from(totpCode(secret, now + w * 30_000)), candidate))
}

export const sha256 = (input: string | Buffer) => createHash('sha256').update(input).digest('hex')
