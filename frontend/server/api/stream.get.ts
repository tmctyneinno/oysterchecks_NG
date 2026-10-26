// Server-Sent Events stream for live UI updates (§10.4). Events are filtered per user so nothing
// leaks across RBAC boundaries; payloads are hints only, clients refetch through the guarded APIs.
export default defineEventHandler(async (event) => {
  const user = await requireUser(event)
  const scope = ROLES[user.role].scope
  const stream = createEventStream(event)

  const visible = (e: LiveEvent) => {
    switch (e.type) {
      case 'alert': return can(user.role, 'alerts:read') && (scope !== 'bu' || e.payload.businessUnit === user.businessUnit)
      case 'notification': return e.payload.userId === user.id
      case 'activity': return can(user.role, 'auditlog:read') || can(user.role, 'dashboard:exec')
      default: return true
    }
  }

  const unsubscribe = subscribe((e) => {
    if (visible(e)) stream.push({ event: e.type, data: JSON.stringify(e.payload) }).catch(() => {})
  })
  const heartbeat = setInterval(() => stream.push({ event: 'ping', data: String(Date.now()) }).catch(() => {}), 25_000)

  // Flush headers straight away so the client shows "Live" without waiting for the first heartbeat.
  stream.push({ event: 'ping', data: String(Date.now()) }).catch(() => {})

  stream.onClosed(async () => {
    clearInterval(heartbeat)
    unsubscribe()
    await stream.close()
  })

  return stream.send()
})
