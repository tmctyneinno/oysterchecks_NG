/** Cached user directory for owner/assignee pickers. */
export function useDirectory() {
  const { data } = useFetch('/api/users/lookup', { key: 'directory', default: () => [], dedupe: 'defer' })
  const nameOf = (id: number | null | undefined) => (id == null ? '-' : data.value.find(u => u.id === id)?.name ?? '-')
  return { users: data, nameOf }
}
