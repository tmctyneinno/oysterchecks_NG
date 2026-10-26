/**
 * Wraps a mutation: tracks pending state, shows a toast on success/failure, and returns the
 * result (or undefined on failure) so callers can close dialogs and refresh only on success.
 */
export function useAction() {
  const pending = ref(false)
  const error = ref<string | null>(null)
  const toast = useToast()

  async function run<T>(fn: () => Promise<T>, successMessage?: string): Promise<T | undefined> {
    pending.value = true
    error.value = null
    try {
      const result = await fn()
      if (successMessage) toast.success(successMessage)
      return result
    }
    catch (err) {
      error.value = errorMessage(err)
      toast.error(error.value)
      return undefined
    }
    finally {
      pending.value = false
    }
  }

  return { run, pending, error }
}

/** Typed POST/PATCH helpers so pages don't repeat method/body boilerplate. */
export const api = {
  post: <T = unknown>(url: string, body: object = {}) => $fetch<T>(url, { method: 'POST', body }),
  patch: <T = unknown>(url: string, body: object) => $fetch<T>(url, { method: 'PATCH', body }),
}
