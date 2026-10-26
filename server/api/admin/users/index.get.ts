export default defineEventHandler(async (event) => {
  await requirePermission(event, 'users:manage')
  return useDb().select().from(tables.users).orderBy(tables.users.name).all()
    .map(({ passwordHash: _p, mfaSecret: _m, ...u }) => ({ ...u, roleLabel: ROLES[u.role].label, locked: !!u.lockedUntil && u.lockedUntil > new Date() }))
})
