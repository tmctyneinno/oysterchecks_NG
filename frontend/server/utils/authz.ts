import type { H3Event } from 'h3'
import type { User } from '#auth-utils'
import { and, eq, type SQL } from 'drizzle-orm'
import type { SQLiteColumn } from 'drizzle-orm/sqlite-core'

export async function requireUser(event: H3Event): Promise<User> {
  const session = await getUserSession(event)
  if (!session.user) throw createError({ statusCode: 401, statusMessage: 'Authentication required' })
  return session.user
}

/** Server-side RBAC enforcement (§6.5). Every API handler calls this, the UI is never trusted. */
export async function requirePermission(event: H3Event, ...permissions: Permission[]): Promise<User> {
  const user = await requireUser(event)
  const missing = permissions.filter(p => !can(user.role, p))
  if (missing.length) {
    throw createError({ statusCode: 403, statusMessage: `Your role does not permit: ${missing.join(', ')}` })
  }
  return user
}

/** Row filter for business-unit scoped roles ("Own BU controls and alerts only"). */
export function buScope(user: User, column: SQLiteColumn): SQL | undefined {
  if (ROLES[user.role].scope !== 'bu') return undefined
  return eq(column, user.businessUnit ?? '__none__')
}

export const whereAll = (...conds: (SQL | undefined)[]) => {
  const list = conds.filter(Boolean) as SQL[]
  return list.length ? and(...list) : undefined
}

export function parseId(event: H3Event, name = 'id') {
  const id = Number(getRouterParam(event, name))
  if (!Number.isInteger(id) || id <= 0) throw createError({ statusCode: 400, statusMessage: 'Invalid id' })
  return id
}

export function notFound(what = 'Record'): never {
  throw createError({ statusCode: 404, statusMessage: `${what} not found` })
}
