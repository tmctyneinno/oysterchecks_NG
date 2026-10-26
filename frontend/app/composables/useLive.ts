// Real-time layer (§10.4): one EventSource per tab. If the stream drops, the UI shows a "Stale"
// state and pages fall back to 30-second polling until it reconnects.

type Handler = (payload: any) => void
type LiveStatus = 'connecting' | 'live' | 'stale'

const handlers = new Map<string, Set<Handler>>()
let source: EventSource | null = null
let retryTimer: ReturnType<typeof setTimeout> | null = null

export function useLiveStatus() {
  return useState<LiveStatus>('live:status', () => 'connecting')
}

function dispatch(type: string, payload: unknown) {
  handlers.get(type)?.forEach(h => h(payload))
  handlers.get('*')?.forEach(h => h({ type, payload }))
}

export function connectLive() {
  if (import.meta.server || source) return
  const status = useLiveStatus()
  status.value = 'connecting'
  source = new EventSource('/api/stream')
  source.onopen = () => { status.value = 'live' }
  source.onerror = () => {
    status.value = 'stale'
    source?.close()
    source = null
    if (retryTimer) clearTimeout(retryTimer)
    retryTimer = setTimeout(connectLive, 5000)
  }
  for (const type of ['alert', 'ccm', 'activity', 'notification', 'data']) {
    source.addEventListener(type, (e) => {
      try { dispatch(type, JSON.parse((e as MessageEvent).data)) }
      catch { /* malformed event, ignore */ }
    })
  }
}

export function disconnectLive() {
  source?.close()
  source = null
  if (retryTimer) clearTimeout(retryTimer)
}

export function onLive(type: string, handler: Handler) {
  if (!handlers.has(type)) handlers.set(type, new Set())
  handlers.get(type)!.add(handler)
  return () => handlers.get(type)!.delete(handler)
}

/**
 * Re-runs `refresh` when any of the given live event types arrive (debounced), and polls every 30s
 * while the stream is stale. Cleans up automatically with the calling component.
 */
export function useLiveRefresh(types: string[], refresh: () => unknown, debounceMs = 800) {
  if (import.meta.server) return
  const status = useLiveStatus()
  let timer: ReturnType<typeof setTimeout> | null = null
  const trigger = () => {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => refresh(), debounceMs)
  }
  const offs = types.map(t => onLive(t, trigger))
  const poll = setInterval(() => { if (status.value !== 'live') refresh() }, 30_000)
  onScopeDispose(() => {
    offs.forEach(off => off())
    clearInterval(poll)
    if (timer) clearTimeout(timer)
  })
}
