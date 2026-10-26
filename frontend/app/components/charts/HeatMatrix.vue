<template>
  <div class="matrix-wrap">
    <div class="y-title">Likelihood</div>
    <div class="matrix" role="grid" :aria-label="`Risk heat matrix (${mode})`">
      <template v-for="l in [5, 4, 3, 2, 1]" :key="l">
        <div class="axis-label num" role="rowheader">{{ l }}</div>
        <button
          v-for="i in [1, 2, 3, 4, 5]"
          :key="`${l}-${i}`"
          type="button"
          role="gridcell"
          class="cell"
          :class="[`band-${bandFor(i * l)}`, { selected: selected && selected.impact === i && selected.likelihood === l }]"
          :aria-label="`Impact ${i}, likelihood ${l}, score ${i * l}: ${cellRisks(i, l).length} risks`"
          @click="emit('select', cellRisks(i, l).length ? { impact: i, likelihood: l } : null)"
        >
          <span class="score num">{{ i * l }}</span>
          <span v-if="cellRisks(i, l).length" class="dots">
            <span v-for="r in cellRisks(i, l).slice(0, 4)" :key="r.id" class="dot" :title="`${r.ref} ${r.title}`">{{ r.ref }}</span>
            <span v-if="cellRisks(i, l).length > 4" class="dot more">+{{ cellRisks(i, l).length - 4 }}</span>
          </span>
        </button>
      </template>
      <div />
      <div v-for="i in [1, 2, 3, 4, 5]" :key="`x${i}`" class="axis-label num" style="text-align:center">{{ i }}</div>
    </div>
    <div class="x-title">Impact</div>
    <div class="legend row-wrap small" aria-label="Legend">
      <span v-for="b in bands" :key="b.key" class="row text-2" style="gap:6px"><span class="swatch" :class="`band-${b.key}`" aria-hidden="true" />{{ b.label }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
interface MatrixRisk { id: number, ref: string, title: string, impact: number, likelihood: number }
const props = defineProps<{ risks: MatrixRisk[], mode: 'inherent' | 'residual', selected?: { impact: number, likelihood: number } | null }>()
const emit = defineEmits<{ select: [{ impact: number, likelihood: number } | null] }>()
const cellRisks = (i: number, l: number) => props.risks.filter(r => r.impact === i && r.likelihood === l)
const bands = [
  { key: 'low', label: 'Low (1–4)' }, { key: 'moderate', label: 'Moderate (5–9)' },
  { key: 'high', label: 'High (10–14)' }, { key: 'critical', label: 'Critical (15–25)' },
]
</script>

<style scoped>
.matrix-wrap { display: grid; grid-template-columns: 18px 1fr; grid-template-rows: 1fr auto auto; }
.y-title { grid-row: 1; writing-mode: vertical-rl; transform: rotate(180deg); font-size: 11px; color: var(--muted); text-align: center; }
.x-title { grid-column: 2; font-size: 11px; color: var(--muted); text-align: center; margin-top: 4px; }
.matrix { grid-column: 2; display: grid; grid-template-columns: 20px repeat(5, minmax(0, 1fr)); gap: 3px; }
.axis-label { font-size: 11px; color: var(--muted); align-self: center; }
.cell { position: relative; aspect-ratio: 1.9; border: 1px solid transparent; border-radius: var(--r-sm); cursor: pointer; padding: 4px; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-start; min-height: 46px; }
.cell:hover { border-color: var(--border-strong); }
.cell.selected { border-color: var(--text); }
.score { font-size: 10px; color: var(--muted); }
.dots { display: flex; flex-wrap: wrap; gap: 3px; }
.dot { font-family: var(--font-mono); font-size: 10.5px; font-weight: 500; background: var(--surface); color: var(--text); border: 1px solid var(--border-strong); border-radius: var(--r-sm); padding: 0 5px; }
.dot.more { color: var(--muted); }
.band-low { background: color-mix(in srgb, var(--ok) 14%, transparent); }
.band-moderate { background: color-mix(in srgb, var(--info) 16%, transparent); }
.band-high { background: color-mix(in srgb, var(--warning) 24%, transparent); }
.band-critical { background: color-mix(in srgb, var(--critical) 28%, transparent); }
.legend { grid-column: 1 / -1; margin-top: 12px; gap: 16px; }
.swatch { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }
</style>
