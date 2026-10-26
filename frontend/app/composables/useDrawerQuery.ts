/**
 * Keeps the selected record in the URL (?id=123) so detail drawers are deep-linkable, e.g. links
 * in notifications and global search open straight to the record.
 */
export function useDrawerQuery(param = 'id') {
  const route = useRoute()
  const router = useRouter()
  const selectedId = computed<number | null>({
    get: () => {
      const v = Number(route.query[param])
      return Number.isInteger(v) && v > 0 ? v : null
    },
    set: (v) => {
      const query = { ...route.query }
      if (v) query[param] = String(v)
      else delete query[param]
      router.replace({ query })
    },
  })
  return selectedId
}
