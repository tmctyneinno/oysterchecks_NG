<template>
  <nav class="app-sidebar" :class="{ open }" aria-label="Primary">
    <div v-for="g in groups" :key="g.group" class="group">
      <div class="group-label">{{ g.group }}</div>
      <NuxtLink v-for="item in g.items" :key="item.to" :to="item.to" class="nav-link" active-class="active" @click="emit('navigate')">
        <i :class="['bi', item.icon]" aria-hidden="true" />
        <span>{{ item.label }}</span>
        <span v-if="item.badge === 'alerts' && openAlerts" class="badge num" :aria-label="`${openAlerts} open alerts`">{{ openAlerts }}</span>
      </NuxtLink>
    </div>
    <div class="sidebar-foot small">
      <div class="muted">Signed in as</div>
      <div>{{ roleDef?.label }}<span v-if="user?.businessUnit" class="muted">, {{ user.businessUnit }}</span></div>
    </div>
  </nav>
</template>

<script setup lang="ts">
defineProps<{ open: boolean }>()
const emit = defineEmits<{ navigate: [] }>()
const { can: allowed, roleDef, user } = useAccess()
const groups = computed(() => NAV.map(g => ({ ...g, items: g.items.filter(i => allowed(i.permission)) })).filter(g => g.items.length))

const { data: alertList, refresh } = useFetch('/api/alerts', { key: 'sidebar-alerts', query: { view: 'open' }, default: () => [], immediate: allowed('alerts:read'), server: false })
const openAlerts = computed(() => alertList.value.length)
useLiveRefresh(['alert'], refresh, 1500)
</script>

<style scoped>
.app-sidebar { position: fixed; top: var(--header-h); left: 0; bottom: 0; width: var(--sidebar-w); background: var(--chrome); border-right: 1px solid var(--border); overflow-y: auto; padding: 14px 10px 16px; z-index: 40; display: flex; flex-direction: column; gap: 16px; }
.group-label { font-size: 12px; font-weight: 500; color: var(--muted); padding: 0 10px 4px; }
.nav-link { display: flex; align-items: center; gap: 10px; padding: 6px 10px; border-radius: var(--r-md); color: var(--text-2); font-size: 13.5px; min-height: 34px; }
.nav-link i { color: var(--muted); font-size: 15px; }
.nav-link:hover { background: var(--surface-2); color: var(--text); text-decoration: none; }
.nav-link.active { background: var(--accent-soft); color: var(--text); font-weight: 500; }
.nav-link.active i { color: var(--accent); }
.badge { margin-left: auto; background: var(--surface-2); color: var(--text-2); border-radius: 10px; font-size: 12px; padding: 0 7px; line-height: 18px; }
.nav-link.active .badge { background: var(--surface); }
.sidebar-foot { margin-top: auto; padding: 12px 10px 0; border-top: 1px solid var(--border); }
@media (max-width: 1024px) {
  .app-sidebar { transform: translateX(-100%); transition: transform 0.2s ease; }
  .app-sidebar.open { transform: none; box-shadow: var(--shadow); }
}
</style>
