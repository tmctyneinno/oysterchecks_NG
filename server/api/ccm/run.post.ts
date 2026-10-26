export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'ccm:manage')
  const summary = runCcmCycle()
  if (!summary) throw createError({ statusCode: 409, statusMessage: 'A CCM cycle is already running' })
  writeAudit(actorOf(user), 'ccm.manual_run', 'ccm', null, summary, event)
  return summary
})
