import { EventEmitter } from 'node:events'

// In-process event bus feeding the SSE stream (§10.4 real-time updates). A multi-instance
// deployment would swap this for Redis pub/sub or Kafka; callers only use publish/subscribe.

export type LiveEvent =
  | { type: 'alert', payload: { id: number, ref: string, severity: string, status: string, title: string, businessUnit: string | null, change: 'created' | 'updated' } }
  | { type: 'ccm', payload: { rulesEvaluated: number, alertsCreated: number, at: string } }
  | { type: 'activity', payload: { action: string, entityType: string, entityId: string | null, actorName: string, at: string } }
  | { type: 'notification', payload: { userId: number, title: string, severity: string } }
  | { type: 'data', payload: { domain: string } }

const g = globalThis as unknown as { __ocBus?: EventEmitter }
const bus = g.__ocBus ??= new EventEmitter().setMaxListeners(500)

export const publish = (e: LiveEvent) => bus.emit('event', e)

export function subscribe(fn: (e: LiveEvent) => void) {
  bus.on('event', fn)
  return () => bus.off('event', fn)
}
