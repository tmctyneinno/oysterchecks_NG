<template>
  <header class="app-header">
    <button class="icon-btn menu-btn" type="button" aria-label="Toggle navigation" @click="emit('toggle-nav')"><i class="bi bi-list" /></button>
    <NuxtLink :to="landing" class="brand" aria-label="OysterChecks home">
      <span class="mark" aria-hidden="true">OC</span>
      <span class="brand-name">OysterChecks</span>
    </NuxtLink>

    <div class="search" role="search">
      <i class="bi bi-search muted" aria-hidden="true" />
      <input
        v-model="q"
        type="search"
        placeholder="Search alerts, risks, controls, policies"
        aria-label="Search"
        @keydown.esc="q = ''"
        @keydown.enter="results[0] && go(results[0].to)"
      >
      <div v-if="q.trim().length >= 2" class="results" role="listbox">
        <button v-for="r in results" :key="r.to" type="button" class="result" role="option" @click="go(r.to)">
          <span class="small muted">{{ r.domain }} <span class="mono">{{ r.ref }}</span></span>
          <span>{{ r.title }}</span>
        </button>
        <div v-if="!results.length && !searching" class="result muted">No results for "{{ q }}"</div>
      </div>
    </div>

    <div class="spacer" />

    <span class="live small" :title="liveTitle" role="status">
      <span class="status-dot" :class="{ stale: status !== 'live' }" aria-hidden="true" />
      <span class="live-text">{{ status === 'live' ? 'Live' : status === 'stale' ? 'Reconnecting' : 'Connecting' }}</span>
    </span>

    <button class="icon-btn" type="button" :aria-label="`${themeLabel}. Click to change`" :title="themeLabel" @click="cycleTheme">
      <i :class="['bi', themeIcon]" />
    </button>

    <button class="icon-btn" type="button" :aria-label="`Notifications${unread ? `, ${unread} unread` : ''}`" @click="panelOpen = true">
      <i class="bi bi-bell" />
      <span v-if="unread" class="dot-badge num">{{ unread > 9 ? '9+' : unread }}</span>
    </button>

    <div class="user">
      <span class="avatar" aria-hidden="true">{{ initials }}</span>
      <span class="user-text">
        <span class="small" style="font-weight:500">{{ user?.name }}</span>
        <span class="small muted">{{ roleDef?.label }}</span>
      </span>
      <button class="icon-btn" type="button" aria-label="Reset demo data" title="Reset demo data" @click="resetDemo"><i class="bi bi-arrow-counterclockwise" /></button>
      <button class="icon-btn" type="button" aria-label="Sign out" title="Sign out" @click="signOut"><i class="bi bi-box-arrow-right" /></button>
    </div>

    <LayoutNotificationPanel :open="panelOpen" :items="notifications?.items ?? []" @close="panelOpen = false" @read="markRead" />
  </header>
</template>

<script setup lang="ts">
import { resetOverlay } from '~/demo/state'

const emit = defineEmits<{ 'toggle-nav': [] }>()
const { user, roleDef, landing } = useAccess()
const { clear } = useUserSession()
const status = useLiveStatus()
const { cycle: cycleTheme, label: themeLabel, icon: themeIcon } = useTheme()

const initials = computed(() => (user.value?.name ?? '').split(' ').map(p => p[0]).slice(0, 2).join(''))
const liveTitle = computed(() => status.value === 'live' ? 'Receiving live updates' : 'Connecting to live updates')

const q = ref('')
const results = ref<{ domain: string, ref: string, title: string, to: string }[]>([])
const searching = ref(false)
const SEARCH_URL = '/api/search'
let searchTimer: ReturnType<typeof setTimeout>
watch(q, (v) => {
  clearTimeout(searchTimer)
  if (v.trim().length < 2) { results.value = []; return }
  searching.value = true
  searchTimer = setTimeout(async () => {
    try { results.value = await $fetch<typeof results.value>(SEARCH_URL, { query: { q: v } }) }
    finally { searching.value = false }
  }, 200)
})
const go = (to: string) => { q.value = ''; navigateTo(to) }

const panelOpen = ref(false)
const { data: notifications, refresh: refreshNotifications } = useFetch('/api/notifications', { key: 'notifications', server: false })
const unread = computed(() => notifications.value?.unread ?? 0)
useLiveRefresh(['notification'], refreshNotifications, 300)
async function markRead(ids?: number[]) {
  await $fetch('/api/notifications/read', { method: 'POST', body: { ids } })
  refreshNotifications()
}

async function signOut() {
  await $fetch('/api/auth/logout', { method: 'POST' })
  disconnectLive()
  await clear()
  await navigateTo('/login')
}

function resetDemo() {
  if (!confirm('Reset the demo? Every change made in this browser (alerts, cases, uploads, audit entries) is discarded.')) return
  resetOverlay()
  location.reload()
}
</script>

<style scoped>
.app-header { position: fixed; inset: 0 0 auto 0; height: var(--header-h); background: var(--chrome); border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; padding: 0 14px; z-index: 50; }
.menu-btn { display: none; }
.brand { display: flex; align-items: center; gap: 9px; color: var(--text); width: calc(var(--sidebar-w) - 24px); flex-shrink: 0; }
.brand:hover { text-decoration: none; }
.mark { width: 28px; height: 28px; border-radius: var(--r-md); background: var(--accent); color: var(--on-accent); font-weight: 600; display: grid; place-items: center; font-size: 12px; }
.brand-name { font-weight: 600; font-size: 15px; }
.search { position: relative; display: flex; align-items: center; gap: 8px; background: var(--surface-2); border: 1px solid transparent; border-radius: var(--r-md); padding: 0 10px; width: min(400px, 40vw); height: 34px; }
.search input { background: none; border: none; outline: none; flex: 1; min-width: 0; }
.search:focus-within { border-color: var(--focus); background: var(--surface); }
.results { position: absolute; top: 40px; left: 0; right: 0; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-md); box-shadow: var(--shadow); max-height: 60vh; overflow-y: auto; padding: 4px; }
.result { display: flex; flex-direction: column; align-items: flex-start; gap: 1px; width: 100%; text-align: left; background: none; border: none; padding: 7px 10px; border-radius: var(--r-sm); cursor: pointer; }
.result:hover, .result:focus { background: var(--surface-2); }
.live { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); padding: 0 6px; }
.dot-badge { position: absolute; top: 3px; right: 1px; background: var(--critical); color: #fff; font-size: 10px; border-radius: 8px; padding: 0 4px; line-height: 15px; }
.user { display: flex; align-items: center; gap: 8px; padding-left: 10px; margin-left: 4px; border-left: 1px solid var(--border); }
.user-text { display: flex; flex-direction: column; line-height: 1.25; }
.avatar { width: 30px; height: 30px; border-radius: 50%; background: var(--surface-2); display: grid; place-items: center; font-size: 12px; font-weight: 600; color: var(--text-2); }
@media (max-width: 1024px) {
  .menu-btn { display: inline-flex; }
  .brand { width: auto; }
}
@media (max-width: 860px) {
  .search, .user-text, .live-text { display: none; }
}
</style>
