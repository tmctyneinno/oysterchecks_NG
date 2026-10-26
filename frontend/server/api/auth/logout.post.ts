export default defineEventHandler(async (event) => {
  const session = await getUserSession(event)
  if (session.user) writeAudit(actorOf(session.user), 'auth.logout', 'user', session.user.id, {}, event)
  await clearUserSession(event)
  return { ok: true }
})
