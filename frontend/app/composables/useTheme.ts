export type ThemeChoice = 'system' | 'light' | 'dark'

const ORDER: ThemeChoice[] = ['system', 'light', 'dark']

/**
 * Theme preference stored in a cookie; an inline script in nuxt.config applies it before the app boots.
 * "system" leaves the attribute off and the CSS follows prefers-color-scheme.
 */
export function useTheme() {
  const choice = useCookie<ThemeChoice>('oc-theme', { default: () => 'system', maxAge: 60 * 60 * 24 * 365, sameSite: 'lax' })
  const cycle = () => { choice.value = ORDER[(ORDER.indexOf(choice.value) + 1) % ORDER.length]! }
  const label = computed(() => ({ system: 'System theme', light: 'Light theme', dark: 'Dark theme' })[choice.value])
  const icon = computed(() => ({ system: 'bi-circle-half', light: 'bi-sun', dark: 'bi-moon' })[choice.value])
  return { choice, cycle, label, icon }
}
