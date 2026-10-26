<template>
  <div class="page">
    <UiPageHeader title="ESG" subtitle="TCFD, CSRD and GRI metrics" />

    <div class="kpi-grid">
      <UiKpiCard v-for="s in statuses" :key="s" :label="labelize(s)" :value="metrics.filter(m => m.status === s).length" :tone="toneFor(s === 'no_data' ? 'neutral' : s)" />
    </div>

    <div class="grid-3">
      <UiPanel v-for="p in pillars" :key="p.key" :title="p.label" :sub="`${metrics.filter(m => m.pillar === p.key).length} metrics`" flush>
        <ul class="metrics">
          <li v-for="m in metrics.filter(x => x.pillar === p.key)" :key="m.id">
            <button type="button" class="metric" :disabled="!allowed('esg:write')" @click="editing = m">
              <div class="row" style="gap:8px">
                <span class="small" style="flex:1;min-width:0;font-weight:600">{{ m.name }}</span>
                <UiPill :value="m.status === 'no_data' ? 'neutral' : m.status" :text="labelize(labelize(m.status))" />
              </div>
              <div class="row small" style="gap:8px">
                <span class="num" style="font-size:18px">{{ m.value ?? '-' }}<span class="muted" style="font-size:12px">{{ m.unit }}</span></span>
                <span class="muted">target {{ m.target ?? '-' }}{{ m.unit }} ({{ m.lowerIsBetter ? 'lower' : 'higher' }} is better)</span>
              </div>
              <div class="small muted">{{ m.framework }} · {{ m.period }} · {{ m.ownerName }} · {{ plural(m.evidenceCount, 'evidence file') }}</div>
            </button>
          </li>
        </ul>
      </UiPanel>
    </div>

    <UiModal :open="!!editing" :title="editing?.name ?? ''" submit-label="Record value" :pending="act.pending.value" @close="editing = null" @submit="save">
      <template v-if="editing">
        <UiField :label="`Value (${editing.unit || 'count'})`" required><input v-model.number="form.value" type="number" step="any" class="input"></UiField>
        <UiField label="Evidence source" hint="Board attestations and regulatory submissions rely on this citation." required><textarea v-model="form.evidenceNote" class="textarea" /></UiField>
        <UiEvidence entity-type="esg_metric" :entity-id="editing.id" can-upload />
      </template>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'esg:read' })
useHead({ title: 'ESG' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/esg', { default: () => [] })
const metrics = computed(() => data.value)
const statuses = ['on_track', 'at_risk', 'off_track', 'no_data'] as const
const pillars = [{ key: 'E', label: 'Environmental' }, { key: 'S', label: 'Social' }, { key: 'G', label: 'Governance' }]
type Metric = (typeof metrics.value)[number]
const editing = ref<Metric | null>(null)
const form = reactive({ value: null as number | null, evidenceNote: '' })
watch(editing, m => { if (m) Object.assign(form, { value: m.value, evidenceNote: m.evidenceNote ?? '' }) })
const act = useAction()
async function save() {
  if (await act.run(() => api.patch(`/api/esg/${editing.value!.id}`, form), 'Metric recorded')) { editing.value = null; refresh() }
}
</script>

<style scoped>
.metrics { list-style: none; margin: 0; padding: 0; }
.metric { width: 100%; text-align: left; background: none; border: none; border-bottom: 1px solid var(--border); padding: 12px 16px; display: flex; flex-direction: column; gap: 4px; cursor: pointer; }
.metric:hover:not(:disabled) { background: var(--surface-2); }
.metric:disabled { cursor: default; }
.metrics li:last-child .metric { border-bottom: none; }
</style>
