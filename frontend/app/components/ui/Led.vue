<template>
  <span class="led" :class="`led-${state}`" role="img" :aria-label="label" :title="label" />
</template>

<script setup lang="ts">
const props = defineProps<{ state: 'pass' | 'warn' | 'fail' | 'unknown' }>()
const label = computed(() => ({ pass: 'Passing', warn: 'Warning', fail: 'Failing', unknown: 'Not evaluated yet' })[props.state])
</script>
