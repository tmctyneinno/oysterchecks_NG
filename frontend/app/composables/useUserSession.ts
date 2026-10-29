import type { DemoUser } from '~/demo/fixtures'
import { readSession, writeSession } from '~/demo/state'

/**
 * Signed-in user for the static demo. Same shape as nuxt-auth-utils' composable, but the session
 * lives in this browser tab (sessionStorage), so each tab can be signed in as a different role.
 */
export function useUserSession() {
  const user = useState<DemoUser | null>('demo:user', () => readSession())
  return {
    user,
    loggedIn: computed(() => !!user.value),
    fetch: async () => { user.value = readSession() },
    clear: async () => {
      writeSession(null)
      user.value = null
    },
  }
}
