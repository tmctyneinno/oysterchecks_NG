<template>
  <div class="stack" style="gap:4px">
    <div v-if="label" class="row small"><span class="text-2">{{ label }}</span><span class="spacer" /><span class="num">{{ display ?? `${Math.round(value)}%` }}</span></div>
    <div class="meter" :class="level" role="meter" :aria-valuenow="Math.round(value)" aria-valuemin="0" aria-valuemax="100" :aria-label="label">
      <span :style="{ width: `${Math.max(0, Math.min(100, value))}%` }" />
    </div>
  </div>
</template>

<script setup lang="ts">
// Fill carries severity: good above `good`, warning above `warn`, otherwise danger.
const props = withDefaults(defineProps<{ value: number, label?: string, display?: string, good?: number, warn?: number }>(), { good: 80, warn: 50 })
const level = computed(() => (props.value >= props.good ? '' : props.value >= props.warn ? 'warn' : 'danger'))
</script>
