<template>
  <div class="page">
    <UiPageHeader title="Cases and investigations" subtitle="One case model for screening, fiat and crypto alerts, through to the SAR or STR decision" />

    <div v-if="overview" class="kpi-grid">
      <UiKpiCard label="Open cases" :value="overview.openCases" tone="purple" :sub="`${overview.highScore} high risk`" />
      <UiKpiCard label="SARs past 7 days" :value="overview.sarOverdue" :tone="overview.sarOverdue ? 'critical' : 'ok'" :sub="overview.avgDaysToSubmit !== null ? `Average ${overview.avgDaysToSubmit} days from decision to NCA` : 'No submissions yet'" />
      <UiKpiCard label="From monitoring" :value="cases.filter(c => c.type === 'transaction').length" sub="Fiat transaction cases" />
      <UiKpiCard label="Digital assets" :value="cases.filter(c => c.type === 'crypto').length" sub="Crypto exposure cases" />
      <UiKpiCard label="False-positive rate" :value="overview.falsePositiveRate ?? '-'" :suffix="overview.falsePositiveRate !== null ? '%' : ''" :tone="(overview.falsePositiveRate ?? 0) > 15 ? 'high' : 'ok'" sub="Target below 15%" />
    </div>

    <UiPanel v-if="overview" title="SAR pipeline" sub="Select a stage to filter the cases below">
      <ol class="pipeline">
        <li v-for="s in overview.pipeline" :key="s.key" :class="{ active: stage === s.key }">
          <button type="button" @click="stage = stage === s.key ? null : s.key">
            <span class="num pipe-count">{{ s.count }}</span><span class="small">{{ s.stage }}</span>
          </button>
        </li>
      </ol>
    </UiPanel>

    <div class="row-wrap">
      <span class="small muted">Type</span>
      <button v-for="t in types" :key="t.key" type="button" class="btn btn-sm" :class="{ 'btn-primary': type === t.key }" @click="type = t.key">{{ t.label }}</button>
    </div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="visible" clickable :active-key="caseId" @row-click="r => caseId = r.id" empty="No cases match this filter">
        <template #cell-ref="{ row }"><div class="mono">{{ row.ref }}</div><div class="cell-sub">{{ fmtDate(row.openedAt) }}</div></template>
        <template #cell-customer="{ row }"><div class="cell-title">{{ row.customer.name }}</div><div class="cell-sub"><span class="mono">{{ row.customer.ref }}</span> · {{ row.customer.country }} · {{ labelize(row.customer.riskRating) }}</div></template>
        <template #cell-type="{ row }"><UiPill tone="neutral" :text="TYPE_LABEL[row.type] ?? labelize(row.type)" /></template>
        <template #cell-score="{ row }"><AmlScore :score="row.score" /></template>
        <template #cell-status="{ row }"><div class="row" style="gap:6px"><UiPill :value="row.status" /><UiPill v-if="row.sarDaysElapsed !== null && row.sarDaysElapsed > 7" value="overdue" :text="`Day ${row.sarDaysElapsed}`" /></div></template>
        <template #cell-analystName="{ row }">{{ row.analystName ?? 'Unassigned' }}</template>
      </UiDataTable>
    </UiPanel>

    <AmlCaseDrawer :id="caseId" @close="caseId = null" @changed="refreshAll" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'aml:read' })
useHead({ title: 'Cases and investigations' })

const { data: overview, refresh: refreshOverview } = await useFetch('/api/aml/overview')
const { data: caseData, refresh: refreshCases } = await useFetch('/api/aml/cases', { default: () => [] })
const refreshAll = () => Promise.all([refreshOverview(), refreshCases()])
useLiveRefresh(['data'], refreshAll, 1000)

const TYPE_LABEL: Record<string, string> = { transaction: 'Fiat transaction', crypto: 'Digital assets', sanctions: 'Sanctions', kyc: 'KYC' }
const cases = computed(() => caseData.value)
const types = computed(() => [{ key: 'all', label: 'All' }, ...[...new Set(cases.value.map(c => c.type))].map(t => ({ key: t, label: TYPE_LABEL[t] ?? labelize(t) }))])
const type = ref('all')
const stage = ref<string | null>(null)
const visible = computed(() => cases.value.filter(c => (!stage.value || c.status === stage.value) && (type.value === 'all' || c.type === type.value)))
const caseId = useDrawerQuery('case')

const columns = [
  { key: 'ref', label: 'Case', width: '120px' },
  { key: 'customer', label: 'Customer', sortValue: (r: any) => r.customer.name },
  { key: 'type', label: 'Type', width: '150px' },
  { key: 'score', label: 'Risk score', width: '150px' },
  { key: 'status', label: 'Status', width: '190px' },
  { key: 'analystName', label: 'Analyst', width: '150px' },
]
</script>

<style scoped>
.pipeline { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
@media (max-width: 760px) { .pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.pipeline button { width: 100%; display: flex; flex-direction: column; align-items: flex-start; gap: 2px; padding: 12px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 10px; cursor: pointer; text-align: left; }
.pipeline li.active button { border-color: var(--purple); background: var(--purple-soft); }
.pipe-count { font-size: 24px; font-weight: 600; }
</style>
