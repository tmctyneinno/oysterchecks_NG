// Route guard. Pages declare `definePageMeta({ permission })`; the API re-checks on every call,
// so this only prevents rendering screens a role cannot use.
declare module '#app' {
  interface PageMeta { permission?: Permission, public?: boolean }
}

export default defineNuxtRouteMiddleware((to) => {
  const { loggedIn, user } = useUserSession()

  if (to.meta.public) {
    if (loggedIn.value && to.path === '/login') return navigateTo(ROLES[user.value!.role].landing)
    return
  }
  if (!loggedIn.value) {
    return navigateTo({ path: '/login', query: to.fullPath !== '/' ? { redirect: to.fullPath } : undefined })
  }
  const landing = ROLES[user.value!.role].landing
  if (to.path === '/') return navigateTo(landing)
  if (to.meta.permission && !can(user.value!.role, to.meta.permission)) {
    useToast().error('Your role does not have access to that screen.')
    return navigateTo(landing)
  }
})
