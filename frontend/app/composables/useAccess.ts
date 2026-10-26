/** Client-side view of the RBAC matrix. Only decides what to render, the server enforces access. */
export function useAccess() {
  const { user, loggedIn } = useUserSession()
  const role = computed(() => user.value?.role ?? null)
  const roleDef = computed(() => (role.value ? ROLES[role.value] : null))
  return {
    user,
    loggedIn,
    role,
    roleDef,
    can: (p: Permission) => can(role.value, p),
    landing: computed(() => roleDef.value?.landing ?? '/login'),
  }
}
