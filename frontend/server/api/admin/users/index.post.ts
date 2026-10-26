import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  email: z.string().email().toLowerCase(),
  name: z.string().trim().min(2).max(120),
  role: z.enum(ROLE_KEYS),
  businessUnit: z.string().nullable(),
  password: z.string().min(12, 'Use at least 12 characters'),
}).refine(b => ROLES[b.role].scope !== 'bu' || b.businessUnit, { message: 'BU-scoped roles need a business unit', path: ['businessUnit'] })

export default defineEventHandler(async (event) => {
  const admin = await requirePermission(event, 'users:manage')
  const { password, ...input } = await validateBody(event, body)
  const db = useDb()
  if (db.select().from(tables.users).where(eq(tables.users.email, input.email)).get()) throw createError({ statusCode: 409, statusMessage: 'A user with that email already exists' })
  const row = db.insert(tables.users).values({ ...input, passwordHash: await hashPassword(password) }).returning({ id: tables.users.id }).get()
  writeAudit(actorOf(admin), 'user.created', 'user', row.id, { email: input.email, role: input.role }, event)
  return row
})
