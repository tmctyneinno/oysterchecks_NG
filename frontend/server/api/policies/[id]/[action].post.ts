import { eq } from 'drizzle-orm'
import { z } from 'zod'

// Policy lifecycle (§4.2): draft → in_review → approved (approver ≠ owner) → new version → … ; retire.
const schemas = {
  submit: z.object({ note: z.string().trim().max(1000).optional() }),
  approve: z.object({ note: z.string().trim().max(1000).optional() }),
  reject: z.object({ note: z.string().trim().min(3).max(1000) }),
  'new-version': z.object({ note: z.string().trim().min(3).max(1000) }),
  retire: z.object({ note: z.string().trim().min(3).max(1000) }),
  acknowledge: z.object({}),
}
type Action = keyof typeof schemas

export default defineEventHandler(async (event) => {
  const action = getRouterParam(event, 'action') as Action
  if (!(action in schemas)) throw createError({ statusCode: 404, statusMessage: 'Unknown action' })
  const needed: Permission = action === 'acknowledge' ? 'governance:read' : action === 'approve' || action === 'reject' ? 'policy:approve' : 'governance:write'
  const user = await requirePermission(event, needed)
  const id = parseId(event)
  const { note } = await validateBody(event, schemas[action]) as Record<string, any>
  const db = useDb()
  const { policies, policyVersions, policyAcknowledgements } = tables
  const p = db.select().from(policies).where(eq(policies.id, id)).get()
  if (!p) notFound('Policy')

  const transition = (from: string[], to: typeof p.status, extra: Partial<typeof p> = {}) => {
    if (!from.includes(p.status)) throw createError({ statusCode: 409, statusMessage: `Cannot ${action} a policy that is ${p.status.replace('_', ' ')}` })
    const updated = db.update(policies).set({ status: to, ...extra }).where(eq(policies.id, id)).returning().get()
    db.insert(policyVersions).values({ policyId: id, version: updated.version, status: to, note: note ?? null, actorId: user.id, at: new Date() }).run()
    return updated
  }

  let result: typeof p
  switch (action) {
    case 'submit':
      result = transition(['draft'], 'in_review')
      notifyRole('CCO', { severity: 'info', title: `Policy ready for approval: ${p.ref}`, body: p.title, link: `/governance?id=${id}` })
      break
    case 'approve':
      if (p.ownerId === user.id) throw createError({ statusCode: 403, statusMessage: 'Policy owners cannot approve their own policy' })
      result = transition(['in_review'], 'approved', { approvedById: user.id, approvedAt: new Date() })
      break
    case 'reject':
      result = transition(['in_review'], 'draft')
      notifyUser(p.ownerId, { severity: 'info', title: `Policy returned: ${p.ref}`, body: note, link: `/governance?id=${id}` })
      break
    case 'new-version':
      result = transition(['approved'], 'draft', { version: p.version + 1, approvedById: null, approvedAt: null })
      break
    case 'retire':
      result = transition(['approved', 'draft'], 'retired')
      break
    case 'acknowledge':
      if (p.status !== 'approved') throw createError({ statusCode: 409, statusMessage: 'Only approved policies can be acknowledged' })
      db.insert(policyAcknowledgements).values({ policyId: id, userId: user.id, version: p.version, at: new Date() }).onConflictDoNothing().run()
      result = p
      break
  }
  writeAudit(actorOf(user), `policy.${action}`, 'policy', id, { ref: p.ref, version: result.version, note }, event)
  return result
})
