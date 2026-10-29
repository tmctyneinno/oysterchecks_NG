// Real-time layer (§10.4). In the static demo the "stream" is the demo API itself: every change made
// in this tab, or in another tab of the same browser, is replayed here so open screens refresh.

type Handler = (payload: any) => void
type LiveStatus = 'connecting' | 'live' | 'stale'

const handlers = new Map<string, Set<Handler>>()

export function useLiveStatus() {
  return useState<LiveStatus>('live:status', () => 'connecting')
}

export function dispatchLive(type: string, payload: unknown) {
  handlers.get(type)?.forEach(h => h(payload))
  handlers.get('*')?.forEach(h => h({ type, payload }))
}

export function connectLive() {
  if (import.meta.server) return
  useLiveStatus().value = 'live'
}

export function disconnectLive() {
  useLiveStatus().value = 'connecting'
}

export function onLive(type: string, handler: Handler) {
  if (!handlers.has(type)) handlers.set(type, new Set())
  handlers.get(type)!.add(handler)
  return () => handlers.get(type)!.delete(handler)
}

/** Re-runs `refresh` when any of the given live event types arrive (debounced). Cleans up with the calling component. */
export function useLiveRefresh(types: string[], refresh: () => unknown, debounceMs = 400) {
  if (import.meta.server) return
  let timer: ReturnType<typeof setTimeout> | null = null
  const trigger = () => {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => refresh(), debounceMs)
  }
  // Any change can affect any screen (an alert sign-off moves dashboard counts, for example).
  const offs = [...new Set([...types, 'data'])].map(t => onLive(t, trigger))
  onScopeDispose(() => {
    offs.forEach(off => off())
    if (timer) clearTimeout(timer)
  })
}
