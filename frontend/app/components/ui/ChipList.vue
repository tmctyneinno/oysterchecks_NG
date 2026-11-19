<template>
  <span v-if="items.length" class="chips">
    <NuxtLink v-for="i in items" :key="i.id" :to="`${to}${i.id}`" class="chip" :title="i[label]"><span class="mono">{{ i.ref }}</span> {{ i[label] }}</NuxtLink>
  </span>
  <span v-else class="muted">None</span>
</template>

<script setup lang="ts">
/** Linked records shown as compact chips, e.g. the obligations a regulatory change affects. */
defineProps<{ items: Record<string, any>[], to: string, label: string }>()
</script>

<style scoped>
.chips { display: flex; flex-wrap: wrap; gap: 6px; }
.chip { display: inline-block; max-width: 100%; padding: 2px 8px; border: 1px solid var(--border); border-radius: 999px; background: var(--surface-2); color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12.5px; }
.chip:hover { text-decoration: none; border-color: var(--border-strong); }
</style>
