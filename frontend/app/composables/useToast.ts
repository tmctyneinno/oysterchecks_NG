export interface Toast { id: number, kind: 'success' | 'error' | 'info', message: string }

let seq = 0

export function useToast() {
  const toasts = useState<Toast[]>('toasts', () => [])
  const push = (kind: Toast['kind'], message: string, ms = 4500) => {
    const id = ++seq
    toasts.value.push({ id, kind, message })
    if (import.meta.client) setTimeout(() => dismiss(id), ms)
  }
  const dismiss = (id: number) => { toasts.value = toasts.value.filter(t => t.id !== id) }
  return {
    toasts,
    dismiss,
    success: (m: string) => push('success', m),
    error: (m: string) => push('error', m, 7000),
    info: (m: string) => push('info', m),
  }
}

/** Extracts the server's statusMessage (or first validation issue) from a $fetch error. */
export function errorMessage(err: unknown): string {
  const e = err as { data?: { statusMessage?: string, message?: string, data?: { issues?: { message: string }[] } }, statusMessage?: string, message?: string }
  const issues = e?.data?.data?.issues
  if (issues?.length) return issues[0]!.message
  return e?.data?.statusMessage || e?.data?.message || e?.statusMessage || e?.message || 'Something went wrong'
}
