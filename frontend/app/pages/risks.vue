<template>
  <div class="page">
    <UiPageHeader title="Risk register" :subtitle="`${risks.length} open risks`">
      <a href="/api/export/risks" class="btn"><i class="bi bi-download" aria-hidden="true" /> Export</a>
      <button v-if="allowed('risk:write')" class="btn btn-primary" type="button" @click="editing = 'new'"><i class="bi bi-plus-lg" aria-hidden="true" /> Add risk</button>
    </UiPageHeader>

    <div class="dash-grid">
      <UiPanel title="Heat matrix" :sub="cell ? `Filtered: impact ${cell.impact} × likelihood ${cell.likelihood}` : 'Click a cell to filter the register'">
        <template #actions>
          <UiTabs v-model="mode" :tabs="[{ key: 'residual', label: 'Residual' }, { key: 'inherent', label: 'Inherent' }]" style="border:none" />
        </template>
        <ChartsHeatMatrix :risks="matrixRisks" :mode="mode as 'inherent' | 'residual'" :selected="cell" @select="c => cell = c" />
      </UiPanel>
      <UiPanel title="Top risks" sub="Residual score (out of 25)">
        <ChartsBarList :rows="top" label="Top residual risks" />
        <div v-if="breachedKris.length" class="stack" style="margin-top:16px;gap:8px">
          <div class="section-title" style="margin:0">KRIs at or over limit</div>
          <div v-for="k in breachedKris" :key="k.id" class="row small" style="gap:8px">
            <UiPill :value="k.status" :text="labelize(k.status)" />
            <span class="text-2" style="flex:1;min-width:0">{{ k.name }}</span>
            <span class="num">{{ k.value }}{{ k.unit }}</span>
          </div>
        </div>
      </UiPanel>
    </div>

    <UiPanel flush :title="cell ? 'Filtered register' : 'Risk register'">
      <template #actions><button v-if="cell" class="btn btn-sm btn-ghost" type="button" @click="cell = null">Clear filter</button></template>
      <UiDataTable :columns="columns" :rows="register" clickable :active-key="selectedId" @row-click="r => selectedId = r.id">
        <template #cell-title="{ row }">
          <div class="cell-title">{{ row.title }}</div>
          <div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.category }} · {{ row.ownerName }}</div>
        </template>
        <template #cell-inherent="{ row }"><span class="num">{{ row.inherent.score }}</span> <span class="muted small">({{ row.impact }}×{{ row.likelihood }})</span></template>
        <template #cell-residual="{ row }"><div class="row" style="gap:6px"><span class="num">{{ row.residual.score }}</span><UiPill :value="bandTone(row.residual.band)" :text="labelize(row.residual.band)" /></div></template>
        <template #cell-controls="{ row }">
          <span v-if="row.controlGap" class="pill tone-high">No controls</span>
          <span v-else class="small">{{ row.controls.length }} mapped · <span class="muted">{{ Math.round(row.residual.reduction * 100) }}% reduction</span></span>
        </template>
        <template #cell-treatment="{ row }"><UiPill tone="neutral" :text="labelize(row.treatment)" /></template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!selected" :title="selected?.title ?? ''" :eyebrow="selected ? `${selected.ref} · ${selected.category}` : ''" @close="selectedId = null">
      <template v-if="selected" #meta>
        <UiPill :value="bandTone(selected.inherent.band)" :text="`Inherent ${selected.inherent.score}`" />
        <UiPill :value="bandTone(selected.residual.band)" :text="`Residual ${selected.residual.score}`" />
        <UiPill v-if="selected.escalatedToBoard" tone="purple" text="Escalated to board" />
      </template>
      <template v-if="selected">
        <p v-if="selected.description" class="text-2">{{ selected.description }}</p>
        <dl class="kv">
          <dt>Owner</dt><dd>{{ selected.ownerName }}</dd>
          <dt>Business unit</dt><dd>{{ selected.businessUnit ?? 'None' }}</dd>
          <dt>Impact × likelihood</dt><dd class="num">{{ selected.impact }} × {{ selected.likelihood }} = {{ selected.inherent.score }}</dd>
          <dt>Residual</dt><dd class="num">{{ selected.residual.impact }} × {{ selected.residual.likelihood }} = {{ selected.residual.score }} <span class="muted">({{ Math.round(selected.residual.reduction * 100) }}% likelihood reduction from controls)</span></dd>
          <dt>Treatment</dt><dd>{{ labelize(selected.treatment) }}<span v-if="selected.treatmentDueAt" class="muted"> · due {{ fmtDate(selected.treatmentDueAt) }}</span></dd>
          <dt>Plan</dt><dd>{{ selected.treatmentPlan ?? 'None' }}</dd>
          <dt>Last reviewed</dt><dd>{{ fmtDate(selected.lastReviewedAt) }}</dd>
        </dl>
        <section>
          <div class="section-title">Mapped controls</div>
          <p v-if="!selected.controls.length" class="callout warn small"><i class="bi bi-exclamation-triangle" aria-hidden="true" /> No controls mapped, so residual risk equals inherent risk.</p>
          <div v-for="c in selected.controls" :key="c.id" class="row small" style="gap:8px;padding:6px 0">
            <NuxtLink :to="`/controls?id=${c.id}`" class="mono">{{ c.ref }}</NuxtLink>
            <span style="flex:1;min-width:0">{{ c.name }}</span>
            <UiPill :value="c.effectiveness" />
          </div>
        </section>
        <section v-if="selected.kris.length">
          <div class="section-title">Key risk indicators</div>
          <div v-for="k in selected.kris" :key="k.id" class="kri">
            <div class="row small">
              <span style="flex:1">{{ k.name }}</span>
              <ChartsSparkline :values="k.history" :color="k.status === 'red' ? 'var(--critical)' : k.status === 'amber' ? 'var(--warning)' : 'var(--accent)'" :label="`${k.name} history`" />
              <span class="num" style="min-width:60px;text-align:right">{{ k.value }}{{ k.unit }}</span>
              <UiPill :value="k.status" :text="labelize(k.status)" />
            </div>
            <div class="small muted">Amber at {{ k.amber }}{{ k.unit }}, red at {{ k.red }}{{ k.unit }}
              <button v-if="allowed('risk:write')" class="btn btn-ghost btn-sm" type="button" @click="kriEdit = { id: k.id, value: k.value }">Record value</button>
            </div>
          </div>
        </section>
      </template>
      <template v-if="selected && allowed('risk:write')" #footer>
        <button class="btn" type="button" @click="editing = selected.id">Edit</button>
        <button class="btn" type="button" @click="markReviewed">Mark reviewed</button>
        <button v-if="!selected.escalatedToBoard" class="btn" type="button" @click="escalate">Escalate to board</button>
      </template>
    </UiDrawer>

    <RisksRiskForm :open="!!editing" :risk="editing === 'new' ? null : risks.find(r => r.id === editing) ?? null" @close="editing = null" @saved="editing = null; refresh()" />

    <UiModal :open="!!kriEdit" title="Record KRI value" submit-label="Record" :pending="act.pending.value" @close="kriEdit = null" @submit="saveKri">
      <UiField v-if="kriEdit" label="Current value" hint="Thresholds are re-evaluated immediately; a red breach raises an alert to the risk owner." required>
        <input v-model.number="kriEdit.value" type="number" step="any" class="input">
      </UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'risk:read' })
useHead({ title: 'Risk register' })
const { can: allowed } = useAccess()

const { data, refresh } = await useFetch('/api/risks', { default: () => [] })
const risks = computed(() => data.value)
useLiveRefresh(['ccm'], refresh, 3000)

const mode = ref('residual')
const cell = ref<{ impact: number, likelihood: number } | null>(null)
const selectedId = useDrawerQuery('id')
const selected = computed(() => risks.value.find(r => r.id === selectedId.value) ?? null)
const editing = ref<number | 'new' | null>(null)

const pos = (r: (typeof risks.value)[number]) => (mode.value === 'residual' ? r.residual : r.inherent)
const matrixRisks = computed(() => risks.value.map(r => ({ id: r.id, ref: r.ref, title: r.title, impact: pos(r).impact, likelihood: pos(r).likelihood })))
const register = computed(() => risks.value.filter(r => !cell.value || (pos(r).impact === cell.value.impact && pos(r).likelihood === cell.value.likelihood)))
const top = computed(() => [...risks.value].sort((a, b) => b.residual.score - a.residual.score).slice(0, 6).map(r => ({ label: `${r.ref} ${r.title}`, value: r.residual.score })))
const breachedKris = computed(() => risks.value.flatMap(r => r.kris).filter(k => k.status !== 'green'))
const bandTone = (b: string) => (b === 'moderate' ? 'medium' : b === 'low' ? 'ok' : b)

const columns = [
  { key: 'title', label: 'Risk' },
  { key: 'inherent', label: 'Inherent', width: '110px', sortValue: (r: any) => r.inherent.score },
  { key: 'residual', label: 'Residual', width: '150px', sortValue: (r: any) => r.residual.score },
  { key: 'controls', label: 'Controls', width: '200px', sortValue: (r: any) => r.controls.length },
  { key: 'treatment', label: 'Treatment', width: '120px' },
]

const act = useAction()
const kriEdit = ref<{ id: number, value: number } | null>(null)
async function saveKri() {
  if (await act.run(() => api.patch(`/api/kris/${kriEdit.value!.id}`, { value: kriEdit.value!.value }), 'KRI recorded')) { kriEdit.value = null; refresh() }
}
async function markReviewed() {
  if (await act.run(() => api.patch(`/api/risks/${selected.value!.id}`, { reviewed: true }), 'Review recorded')) refresh()
}
async function escalate() {
  if (await act.run(() => api.patch(`/api/risks/${selected.value!.id}`, { escalatedToBoard: true }), 'Escalated. Board members have been notified.')) refresh()
}
</script>

<style scoped>
.kri { padding: 8px 0; border-bottom: 1px solid var(--border); }
.kri:last-child { border-bottom: none; }
</style>
