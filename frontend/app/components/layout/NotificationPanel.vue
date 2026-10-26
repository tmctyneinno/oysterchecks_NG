<template>
  <UiDrawer :open="open" title="Notifications" @close="emit('close')">
    <div class="row">
      <span class="small muted">{{ items.filter(i => !i.readAt).length }} unread</span>
      <span class="spacer" />
      <button class="btn btn-ghost btn-sm" type="button" @click="emit('read')">Mark all read</button>
    </div>
    <ul v-if="items.length" class="list">
      <li v-for="n in items" :key="n.id" :class="{ unread: !n.readAt }">
        <NuxtLink v-if="n.link" :to="n.link" class="item" @click="follow(n.id)">
          <UiPill :value="n.severity === 'info' ? 'neutral' : n.severity" :text="labelize(n.severity)" />
          <span class="title">{{ n.title }}</span>
          <span v-if="n.body" class="small text-2">{{ n.body }}</span>
          <span class="small muted">{{ fmtRelative(n.at) }}</span>
        </NuxtLink>
      </li>
    </ul>
    <div v-else class="empty"><i class="bi bi-bell-slash" /><span>No notifications yet</span></div>
  </UiDrawer>
</template>

<script setup lang="ts">
defineProps<{ open: boolean, items: { id: number, at: string | Date, severity: string, title: string, body: string | null, link: string | null, readAt: string | Date | null }[] }>()
const emit = defineEmits<{ close: [], read: [ids?: number[]] }>()
const follow = (id: number) => { emit('read', [id]); emit('close') }
</script>

<style scoped>
.list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.item { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; padding: 10px 12px; border-radius: var(--r-md); border: 1px solid var(--border); color: var(--text); }
.item:hover { background: var(--surface); text-decoration: none; }
.unread .item { border-color: var(--border-strong); background: var(--info-soft); }
.title { font-weight: 600; font-size: 13px; }
</style>
