<template>
  <div class="stack" style="gap:10px">
    <!-- Legend: always present for ≥2 series (identity never colour-only) -->
    <div class="row-wrap small" role="list" aria-label="Legend">
      <span v-for="s in series" :key="s.key" class="row text-2" role="listitem" style="gap:6px">
        <span :style="{ width: '14px', height: '2px', background: s.color, borderRadius: '2px' }" aria-hidden="true" />{{ s.label }}
      </span>
      <span class="spacer" />
      <button class="btn btn-ghost btn-sm no-print" type="button" @click="showTable = !showTable">
        <i :class="['bi', showTable ? 'bi-graph-up' : 'bi-table']" aria-hidden="true" /> {{ showTable ? 'Chart' : 'Table' }}
      </button>
    </div>

    <div v-show="!showTable" ref="wrap" class="chart-wrap" @mousemove="onMove" @mouseleave="hover = null">
      <svg :width="w" :height="height" role="img" :aria-label="ariaLabel">
        <g>
          <line v-for="t in ticks" :key="t" :x1="padL" :x2="w - padR" :y1="y(t)" :y2="y(t)" stroke="var(--grid)" stroke-width="1" />
          <text v-for="t in ticks" :key="`l${t}`" :x="padL - 8" :y="y(t) + 4" text-anchor="end" class="axis">{{ fmtCompact(t) }}</text>
          <text v-for="(d, i) in data" v-show="i % labelEvery === 0 || i === data.length - 1" :key="d.date" :x="x(i)" :y="height - 6" text-anchor="middle" class="axis">{{ shortDate(d.date) }}</text>
        </g>
        <line v-if="hover !== null" :x1="x(hover)" :x2="x(hover)" :y1="padT" :y2="height - padB" stroke="var(--border-strong)" stroke-width="1" />
        <g v-for="s in series" :key="s.key">
          <path :d="path(s.key)" fill="none" :stroke="s.color" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
          <circle :cx="x(data.length - 1)" :cy="y(Number(data.at(-1)?.[s.key] ?? 0))" r="4" :fill="s.color" stroke="var(--surface)" stroke-width="2" />
          <circle v-if="hover !== null" :cx="x(hover)" :cy="y(Number(data[hover]?.[s.key] ?? 0))" r="4" :fill="s.color" stroke="var(--surface)" stroke-width="2" />
        </g>
      </svg>
      <div v-if="hover !== null && data[hover]" class="chart-tooltip" :style="tooltipStyle">
        <div class="mono small muted" style="margin-bottom:4px">{{ fmtDate(data[hover]!.date) }}</div>
        <div v-for="s in series" :key="s.key" class="row small" style="gap:8px">
          <span :style="{ width: '8px', height: '8px', borderRadius: '50%', background: s.color }" aria-hidden="true" />
          <span class="text-2">{{ s.label }}</span><span class="spacer" /><span class="num">{{ data[hover]![s.key] }}</span>
        </div>
      </div>
    </div>

    <div v-if="showTable" class="table-wrap">
      <table class="table">
        <thead><tr><th>Date</th><th v-for="s in series" :key="s.key" style="text-align:right">{{ s.label }}</th></tr></thead>
        <tbody><tr v-for="d in data" :key="d.date"><td>{{ fmtDate(d.date) }}</td><td v-for="s in series" :key="s.key" class="num" style="text-align:right">{{ d[s.key] }}</td></tr></tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
type Row = { date: string } & Record<string, number | string>
const props = withDefaults(defineProps<{ data: Row[], series: { key: string, label: string, color: string }[], height?: number, ariaLabel?: string }>(), { height: 200, ariaLabel: 'Trend chart' })

const wrap = ref<HTMLElement | null>(null)
const w = ref(600)
const showTable = ref(false)
const hover = ref<number | null>(null)
const padL = 36, padR = 12, padT = 10, padB = 24

let ro: ResizeObserver | null = null
onMounted(() => {
  if (!wrap.value) return
  ro = new ResizeObserver(([e]) => { w.value = Math.max(240, Math.floor(e!.contentRect.width)) })
  ro.observe(wrap.value)
})
onBeforeUnmount(() => ro?.disconnect())

const max = computed(() => {
  const m = Math.max(1, ...props.data.flatMap(d => props.series.map(s => Number(d[s.key]) || 0)))
  const step = m <= 5 ? 1 : m <= 10 ? 2 : m <= 25 ? 5 : 10
  return Math.ceil(m / step) * step
})
const ticks = computed(() => { const n = 4; return Array.from({ length: n + 1 }, (_, i) => Math.round((max.value / n) * i)) })
const x = (i: number) => padL + (i * (w.value - padL - padR)) / Math.max(1, props.data.length - 1)
const y = (v: number) => padT + (1 - v / max.value) * (props.height - padT - padB)
const path = (key: string) => props.data.map((d, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(Number(d[key]) || 0).toFixed(1)}`).join('')
const labelEvery = computed(() => Math.ceil(props.data.length / Math.max(2, Math.floor(w.value / 70))))
const shortDate = (d: string) => new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })

function onMove(e: MouseEvent) {
  const rect = wrap.value!.getBoundingClientRect()
  const px = e.clientX - rect.left
  const i = Math.round(((px - padL) / (w.value - padL - padR)) * (props.data.length - 1))
  hover.value = Math.max(0, Math.min(props.data.length - 1, i))
}
const tooltipStyle = computed(() => {
  if (hover.value === null) return {}
  const left = x(hover.value)
  return left > w.value - 180 ? { right: `${w.value - left + 12}px`, top: '8px' } : { left: `${left + 12}px`, top: '8px' }
})
</script>

<style scoped>
.chart-wrap { position: relative; width: 100%; }
.axis { fill: var(--muted); font-family: var(--font-mono); font-size: 10px; }
</style>
