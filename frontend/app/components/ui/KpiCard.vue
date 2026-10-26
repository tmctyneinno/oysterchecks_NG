<template>
  <component :is="to ? NuxtLink : 'div'" :to="to" class="kpi" :class="{ clickable: !!to }">
    <div class="kpi-label">
      <span v-if="tone === 'critical' || tone === 'high'" class="flag" :class="tone" aria-hidden="true" />{{ label }}
    </div>
    <div class="kpi-value">{{ value }}<sub v-if="suffix">{{ suffix }}</sub></div>
    <div v-if="sub || $slots.sub" class="kpi-sub"><slot name="sub">{{ sub }}</slot></div>
    <ChartsSparkline v-if="trend?.length" class="kpi-spark" :values="trend" :color="lineColor" :label="`${label} trend`" :width="72" :height="26" />
  </component>
</template>

<script setup lang="ts">
const NuxtLink = resolveComponent('NuxtLink')
const props = defineProps<{
  label: string
  value: string | number
  suffix?: string
  sub?: string
  /** Only critical/high draw attention (a small marker); other tones stay neutral on purpose. */
  tone?: Tone
  trend?: number[]
  to?: string
}>()
const lineColor = computed(() => (props.tone === 'critical' ? 'var(--critical)' : props.tone === 'high' ? 'var(--warning)' : 'var(--neutral)'))
</script>

<style scoped>
.clickable { transition: border-color 0.12s; }
.clickable:hover { border-color: var(--border-strong); text-decoration: none; }
.kpi-label { display: flex; align-items: center; gap: 6px; }
.flag { width: 6px; height: 6px; border-radius: 50%; }
.flag.critical { background: var(--critical); }
.flag.high { background: var(--warning); }
</style>
