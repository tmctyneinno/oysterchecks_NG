<template>
  <div class="page">
    <UiPageHeader title="Compliance" :subtitle="`${obligations.length} obligations, ${compliantPct}% compliant`">
      <a href="/api/export/obligations" class="btn"><i class="bi bi-file-earmark-zip" aria-hidden="true" /> Evidence pack (CSV)</a>
      <button v-if="allowed('compliance:write')" class="btn btn-primary" type="button" @click="formOpen = 'new'"><i class="bi bi-plus-lg" aria-hidden="true" /> Add obligation</button>
    </UiPageHeader>

    <UiTabs v-model="tab" :tabs="[{ key: 'obligations', label: 'Obligations', count: obligations.length }, { key: 'reports', label: 'Regulatory returns', count: reports.filter(r => !r.submittedAt).length }]" />

    <template v-if="tab === 'obligations'">
      <div class="kpi-grid">
        <UiKpiCard v-for="s in statuses" :key="s" :label="labelize(s)" :value="obligations.filter(o => o.status === s).length" :tone="toneFor(s)" />
      </div>
      <div class="row-wrap">
        <span class="small muted">Framework</span>
        <button v-for="f in ['All', ...frameworks]" :key="f" type="button" class="btn btn-sm" :class="{ 'btn-primary': framework === f }" @click="framework = f">{{ f }}</button>
      </div>
      <UiPanel flush>
        <UiDataTable :columns="columns" :rows="visible" clickable :active-key="selectedId" @row-click="r => selectedId = r.id">
          <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ labelize(row.sourceType) }} · {{ row.regulator ?? 'Internal' }}</div></template>
          <template #cell-status="{ row }"><UiPill :value="row.status" /></template>
          <template #cell-coverage="{ row }"><span class="small">{{ plural(row.controlIds.length, 'control') }}, {{ plural(row.evidenceCount, 'file') }}</span></template>
          <template #cell-nextReviewAt="{ row }"><span class="small">{{ fmtDate(row.nextReviewAt) }}</span></template>
        </UiDataTable>
      </UiPanel>
    </template>

    <UiPanel v-else flush title="Regulatory returns" sub="CCM-008 raises an alert at T-3 days if submission is not confirmed">
      <UiDataTable :columns="reportColumns" :rows="reports">
        <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub">{{ row.regulator }} · {{ row.frequency }} · {{ row.automated ? 'Auto-generated' : 'Manual' }} · {{ row.ownerName }}</div></template>
        <template #cell-dueAt="{ row }"><span class="mono small">{{ fmtDate(row.dueAt) }}</span><div class="cell-sub">{{ fmtRelative(row.dueAt) }}</div></template>
        <template #cell-state="{ row }"><UiPill :value="row.state" /></template>
        <template #cell-progress="{ row }">
          <span v-if="row.submittedAt" class="small">Submitted {{ fmtDate(row.submittedAt) }} · <span class="mono">{{ row.reference }}</span></span>
          <span v-else-if="row.preparedAt" class="small">Prepared {{ fmtDate(row.preparedAt) }}</span>
          <span v-else class="small muted">Not prepared</span>
        </template>
        <template #cell-actions="{ row }">
          <div v-if="!row.submittedAt" class="row" style="gap:6px;justify-content:flex-end">
            <button v-if="!row.preparedAt && allowed('compliance:write')" class="btn btn-sm" type="button" @click="prepare(row.id)">Generate</button>
            <button v-if="allowed('regreport:submit')" class="btn btn-sm btn-primary" type="button" @click="submitting = row.id">Confirm submission</button>
          </div>
        </template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!selected" :title="selected?.title ?? ''" :eyebrow="selected ? `${selected.ref} · ${selected.framework}` : ''" @close="selectedId = null">
      <template v-if="selected" #meta><UiPill :value="selected.status" /><UiPill tone="neutral" :text="labelize(selected.sourceType)" /></template>
      <template v-if="selected">
        <dl class="kv">
          <dt>Regulator</dt><dd>{{ selected.regulator ?? 'None' }}</dd>
          <dt>Jurisdiction</dt><dd>{{ selected.jurisdiction }}</dd>
          <dt>Owner</dt><dd>{{ selected.ownerName }}</dd>
          <dt>Last assessed</dt><dd>{{ fmtDate(selected.lastAssessedAt) }}</dd>
          <dt>Next review</dt><dd>{{ fmtDate(selected.nextReviewAt) }}</dd>
          <dt>Mapped controls</dt><dd>{{ selected.controlIds.length || 'None' }}</dd>
        </dl>
        <section v-if="allowed('compliance:write')">
          <div class="section-title">Record assessment</div>
          <div class="row-wrap">
            <button v-for="s in statuses" :key="s" type="button" class="btn btn-sm" :class="{ 'btn-primary': selected.status === s }" @click="assess(s)">{{ labelize(s) }}</button>
          </div>
        </section>
        <UiEvidence entity-type="obligation" :entity-id="selected.id" :can-upload="allowed('compliance:write')" />
      </template>
      <template v-if="selected && allowed('compliance:write')" #footer><button class="btn" type="button" @click="formOpen = selected.id">Edit &amp; map controls</button></template>
    </UiDrawer>

    <UiModal :open="!!submitting" title="Confirm regulatory submission" submit-label="Confirm submitted" :pending="act.pending.value" @close="submitting = null" @submit="submit">
      <UiField label="Regulator submission reference" hint="e.g. the RegData / Gabriel confirmation number" required><input v-model="reference" class="input mono"></UiField>
    </UiModal>

    <ComplianceObligationForm :open="!!formOpen" :obligation="formOpen === 'new' ? null : obligations.find(o => o.id === formOpen) ?? null" @close="formOpen = null" @saved="formOpen = null; refresh()" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'compliance:read' })
useHead({ title: 'Compliance' })
const route = useRoute()
const { can: allowed } = useAccess()

const { data: oData, refresh: refreshObligations } = await useFetch('/api/obligations', { default: () => [] })
const { data: rData, refresh: refreshReports } = await useFetch('/api/regulatory-reports', { default: () => [] })
const obligations = computed(() => oData.value)
const reports = computed(() => rData.value)
const refresh = () => Promise.all([refreshObligations(), refreshReports()])
useLiveRefresh(['ccm'], refreshReports, 3000)

const tab = ref(route.query.tab === 'reports' ? 'reports' : 'obligations')
const statuses = ['compliant', 'partial', 'non_compliant', 'not_assessed'] as const
const frameworks = computed(() => [...new Set(obligations.value.map(o => o.framework))].sort())
const framework = ref('All')
const visible = computed(() => obligations.value.filter(o => framework.value === 'All' || o.framework === framework.value))
const compliantPct = computed(() => obligations.value.length ? Math.round(100 * obligations.value.filter(o => o.status === 'compliant').length / obligations.value.length) : 0)
const selectedId = useDrawerQuery('id')
const selected = computed(() => obligations.value.find(o => o.id === selectedId.value) ?? null)
const formOpen = ref<number | 'new' | null>(null)

const columns = [
  { key: 'title', label: 'Obligation' },
  { key: 'framework', label: 'Framework', width: '170px' },
  { key: 'status', label: 'Status', width: '140px' },
  { key: 'coverage', label: 'Coverage', width: '240px', sortable: false },
  { key: 'nextReviewAt', label: 'Next review', width: '120px' },
]
const reportColumns = [
  { key: 'name', label: 'Return' },
  { key: 'dueAt', label: 'Due', width: '140px', sortValue: (r: any) => new Date(r.dueAt).getTime() },
  { key: 'state', label: 'Status', width: '140px' },
  { key: 'progress', label: 'Progress', width: '240px', sortable: false },
  { key: 'actions', label: '', width: '230px', sortable: false },
]

const act = useAction()
async function assess(status: string) {
  if (await act.run(() => api.patch(`/api/obligations/${selected.value!.id}`, { status }), 'Assessment recorded')) refresh()
}
async function prepare(id: number) {
  if (await act.run(() => api.post(`/api/regulatory-reports/${id}/prepare`), 'Return generated from platform data')) refresh()
}
const submitting = ref<number | null>(null)
const reference = ref('')
async function submit() {
  if (await act.run(() => api.post(`/api/regulatory-reports/${submitting.value}/submit`, { reference: reference.value }), 'Submission confirmed')) {
    submitting.value = null
    reference.value = ''
    refresh()
  }
}
</script>
