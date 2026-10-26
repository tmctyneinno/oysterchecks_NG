<template>
  <svg :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`" role="img" :aria-label="label">
    <path :d="area" :fill="color" opacity="0.1" />
    <path :d="line" fill="none" :stroke="color" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
    <circle v-if="last" :cx="last[0]" :cy="last[1]" r="3" :fill="color" stroke="var(--surface)" stroke-width="2" />
  </svg>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{ values: number[], color?: string, width?: number, height?: number, label?: string }>(), { color: 'var(--accent)', width: 84, height: 30 })
const pts = computed(() => {
  const v = props.values
  const max = Math.max(...v), min = Math.min(...v)
  const span = max - min || 1
  const pad = 4
  return v.map((y, i) => [pad + (i * (props.width - pad * 2)) / Math.max(1, v.length - 1), pad + (1 - (y - min) / span) * (props.height - pad * 2)] as const)
})
const line = computed(() => pts.value.map((p, i) => `${i ? 'L' : 'M'}${p[0].toFixed(1)},${p[1].toFixed(1)}`).join(''))
const area = computed(() => pts.value.length ? `${line.value}L${pts.value.at(-1)![0]},${props.height}L${pts.value[0]![0]},${props.height}Z` : '')
const last = computed(() => pts.value.at(-1))
</script>
