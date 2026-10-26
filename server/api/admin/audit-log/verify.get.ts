export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'auditlog:read')
  const result = verifyAuditChain()
  writeAudit(actorOf(user), 'auditlog.verified', 'audit_log', null, result, event)
  return result
})
