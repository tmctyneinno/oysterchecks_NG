<template>
  <ul class="bars" :aria-label="label">
    <li v-for="r in rows" :key="r.label" class="bar-row" :title="`${r.label}: ${r.value}${r.sub ? ` (${r.sub})` : ''}`">
      <div class="row small" style="gap:8px">
        <span class="text-2" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ r.label }}</span>
        <span class="spacer" />
        <span v-if="r.sub" class="muted">{{ r.sub }}</span>
        <span class="num">{{ r.value }}</span>
      </div>
      <div class="track"><span :style="{ width: `${(100 * r.value) / max}%`, background: color }" /></div>
    </li>
  </ul>
</template>

<script setup lang="ts">
// Single-series comparison: one colour, value shown at the end of each bar.
const props = withDefaults(defineProps<{ rows: { label: string, value: number, sub?: string }[], color?: string, label?: string }>(), { color: 'var(--info)', label: 'Bar chart' })
const max = computed(() => Math.max(1, ...props.rows.map(r => r.value)))
</script>

<style scoped>
.bars { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.track { height: 8px; margin-top: 5px; border-radius: 0 4px 4px 0; background: transparent; }
.track span { display: block; height: 100%; border-radius: 0 4px 4px 0; min-width: 2px; }
</style>
