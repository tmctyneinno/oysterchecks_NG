import { eq } from 'drizzle-orm'

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'governance:write')
  const id = parseId(event)
  const { riskIds, controlIds, obligationIds, ...patch } = await validateBody(event, policyBody.partial())
  const { policies } = tables
  const policy = useDb().select().from(policies).where(eq(policies.id, id)).get()
  if (!policy) notFound('Policy')
  // Approved text is frozen; content edits require a new version (version history is permanent).
  if (policy.status === 'approved' && (patch.title || patch.summary)) throw createError({ statusCode: 409, statusMessage: 'Approved policies are read-only. Start a new version to edit.' })
  const updated = useDb().update(policies).set(patch).where(eq(policies.id, id)).returning().get()
  if (riskIds) setLinks('policy', id, 'risk', riskIds)
  if (controlIds) setLinks('policy', id, 'control', controlIds)
  if (obligationIds) setLinks('policy', id, 'obligation', obligationIds)
  writeAudit(actorOf(user), 'policy.updated', 'policy', id, { ref: policy.ref, fields: Object.keys(patch) }, event)
  return updated
})
