<template>
  <div class="page">
    <UiPageHeader title="Vetting" subtitle="BPSS, DBS and BS7858 screening for new starters" />

    <div class="kpi-grid">
      <UiKpiCard label="At risk (start ≤ 3 days)" :value="people.filter(p => p.atRisk).length" :tone="people.some(p => p.atRisk) ? 'critical' : 'ok'" />
      <UiKpiCard label="In progress" :value="people.filter(p => p.vettingStatus === 'in_progress').length" tone="medium" />
      <UiKpiCard label="Not started" :value="people.filter(p => p.vettingStatus === 'not_started').length" tone="high" />
      <UiKpiCard label="Cleared" :value="people.filter(p => p.vettingStatus === 'cleared').length" tone="ok" />
    </div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="people">
        <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.jobTitle }} · {{ row.department }}</div></template>
        <template #cell-startDate="{ row }"><span class="small">{{ fmtDate(row.startDate) }}</span><div class="cell-sub">{{ row.daysToStart > 0 ? `in ${plural(row.daysToStart, 'day')}` : 'Started' }}</div></template>
        <template #cell-vettingType="{ row }"><UiPill tone="neutral" :text="row.vettingType" /></template>
        <template #cell-vettingStatus="{ row }"><div class="row" style="gap:6px"><UiPill :value="row.vettingStatus" /><UiPill v-if="row.atRisk" value="critical" text="Start at risk" /></div></template>
        <template #cell-actions="{ row }">
          <select v-if="allowed('hr:write') && !row.terminatedAt" class="select" style="width:auto" :value="row.vettingStatus" :aria-label="`Vetting status for ${row.name}`" @change="update(row.id, ($event.target as HTMLSelectElement).value)">
            <option v-for="s in ['not_started', 'in_progress', 'cleared', 'failed']" :key="s" :value="s">{{ labelize(s) }}</option>
          </select>
        </template>
      </UiDataTable>
    </UiPanel>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'hr:read' })
useHead({ title: 'Vetting' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/vetting', { default: () => [] })
const people = computed(() => data.value)
const columns = [
  { key: 'name', label: 'Employee' },
  { key: 'businessUnit', label: 'Business unit', width: '160px' },
  { key: 'startDate', label: 'Start date', width: '140px', sortValue: (r: any) => new Date(r.startDate).getTime() },
  { key: 'vettingType', label: 'Standard', width: '110px' },
  { key: 'vettingStatus', label: 'Status', width: '240px' },
  { key: 'actions', label: '', width: '170px', sortable: false },
]
const act = useAction()
async function update(id: number, vettingStatus: string) {
  if (await act.run(() => api.patch(`/api/vetting/${id}`, { vettingStatus }), 'Vetting status updated')) refresh()
}
</script>
