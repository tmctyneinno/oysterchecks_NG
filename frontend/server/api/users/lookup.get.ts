import { eq } from 'drizzle-orm'

/** Minimal directory for owner/assignee pickers. */
export default defineEventHandler(async (event) => {
  await requireUser(event)
  const { users } = tables
  return useDb().select({ id: users.id, name: users.name, role: users.role, businessUnit: users.businessUnit })
    .from(users).where(eq(users.active, true)).orderBy(users.name).all()
    .map(u => ({ ...u, roleLabel: ROLES[u.role].label }))
})
