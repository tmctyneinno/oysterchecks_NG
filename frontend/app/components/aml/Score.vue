<template>
  <span class="score" :aria-label="`Risk score ${score} of 100`">
    <span class="num">{{ score }}</span>
    <span class="bar" aria-hidden="true"><span :style="{ width: `${score}%`, background: color }" /></span>
  </span>
</template>

<script setup lang="ts">
const props = defineProps<{ score: number }>()
const color = computed(() => (props.score >= 75 ? 'var(--critical)' : props.score >= 45 ? 'var(--warning)' : 'var(--info)'))
</script>

<style scoped>
.score { display: inline-flex; align-items: center; gap: 8px; }
.score .num { min-width: 22px; font-weight: 600; }
.bar { width: 70px; height: 6px; border-radius: 3px; background: var(--surface-2); overflow: hidden; }
.bar span { display: block; height: 100%; border-radius: 3px; }
</style>
