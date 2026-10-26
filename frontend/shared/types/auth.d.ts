import type { RoleKey } from '../utils/rbac'

declare module '#auth-utils' {
  interface User {
    id: number
    name: string
    email: string
    role: RoleKey
    businessUnit: string | null
  }

  interface UserSession {
    /** Set after a correct password while the second factor is outstanding. No `user` exists yet. */
    pendingMfa?: { userId: number, enroll: boolean }
    loggedInAt?: number
  }

  interface SecureSessionData {
    /** TOTP secret being enrolled; only persisted to the user once a valid code is confirmed. */
    enrollSecret?: string
  }
}

export {}
