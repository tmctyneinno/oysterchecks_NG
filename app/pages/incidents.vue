<template>
  <div class="page">
    <UiPageHeader title="Incidents" :subtitle="`${openIncidents} open, ${overdueActions} overdue actions`">
      <a href="/api/export/incidents" class="btn"><i class="bi bi-download" aria-hidden="true" /> Export</a>
      <button v-if="allowed('incidents:write')" class="btn btn-primary" type="button" @click="newOpen = true"><i class="bi bi-plus-lg" aria-hidden="true" /> Log incident</button>
    </UiPageHeader>

    <UiTabs v-model="tab" :tabs="[{ key: 'incidents', label: 'Incidents', count: incidents.length }, { key: 'actions', label: 'Corrective actions', count: actions.filter(a => a.status !== 'done').length }]" />

    <UiPanel v-if="tab === 'incidents'" flush>
      <UiDataTable :columns="columns" :rows="incidents" clickable :active-key="selectedId" @row-click="r => selectedId = r.id">
        <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ labelize(row.type) }} · {{ row.businessUnit }}</div></template>
        <template #cell-severity="{ row }"><UiSeverity :value="row.severity" /></template>
        <template #cell-status="{ row }"><div class="row-wrap" style="gap:6px"><UiPill :value="row.status" /><UiPill v-if="row.notificationOverdue" value="overdue" :text="`${row.regulator} notice overdue`" /><UiPill v-else-if="row.notifiable && !row.notifiedAt" value="high" :text="`Notify ${row.regulator}`" /></div></template>
        <template #cell-actions="{ row }"><span class="small">{{ row.actionsOpen }} of {{ row.actionsTotal }} open</span></template>
        <template #cell-reportedAt="{ row }"><span class="mono small">{{ fmtDateTime(row.reportedAt) }}</span></template>
      </UiDataTable>
    </UiPanel>

    <UiPanel v-else flush>
      <UiDataTable :columns="actionColumns" :rows="actions">
        <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · from {{ row.sourceType }} #{{ row.sourceId }}</div></template>
        <template #cell-dueAt="{ row }"><span class="small" :style="row.overdue ? 'color:var(--critical)' : ''">{{ fmtDate(row.dueAt) }}</span></template>
        <template #cell-status="{ row }"><div class="row" style="gap:6px"><UiPill :value="row.status" /><UiPill v-if="row.overdue" value="overdue" text="Overdue" /></div></template>
        <template #cell-ctl="{ row }">
          <div v-if="row.status !== 'done' && (row.ownerId === user?.id || allowed('incidents:write'))" class="row" style="gap:4px;justify-content:flex-end">
            <button v-if="row.status === 'open'" class="btn btn-sm" type="button" @click="updateAction(row.id, { status: 'in_progress' })">Start</button>
            <button class="btn btn-sm btn-primary" type="button" @click="completing = row.id">Complete</button>
          </div>
          <span v-else-if="row.completedAt" class="small muted">Done {{ fmtDate(row.completedAt) }}</span>
        </template>
      </UiDataTable>
    </UiPanel>

    <IncidentsIncidentDrawer :id="selectedId" @close="selectedId = null" @changed="refreshAll" />
    <IncidentsNewIncidentModal :open="newOpen" @close="newOpen = false" @created="(id: number) => { newOpen = false; refreshAll(); selectedId = id }" />

    <UiModal :open="!!completing" title="Complete action" submit-label="Mark done" :pending="act.pending.value" @close="completing = null" @submit="updateAction(completing!, { status: 'done', evidenceNote: evidence })">
      <UiField label="Evidence of completion" hint="e.g. change reference, test result, document location" required><textarea v-model="evidence" class="textarea" /></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'incidents:read' })
useHead({ title: 'Incidents' })
const route = useRoute()
const { can: allowed, user } = useAccess()
const { data: iData, refresh: refreshIncidents } = await useFetch('/api/incidents', { default: () => [] })
const { data: aData, refresh: refreshActions } = await useFetch('/api/actions', { default: () => [] })
const incidents = computed(() => iData.value)
const actions = computed(() => aData.value)
const refreshAll = () => Promise.all([refreshIncidents(), refreshActions()])

const tab = ref(route.query.tab === 'actions' ? 'actions' : 'incidents')
const selectedId = useDrawerQuery('id')
const newOpen = ref(false)
const openIncidents = computed(() => incidents.value.filter(i => i.status !== 'closed').length)
const overdueActions = computed(() => actions.value.filter(a => a.overdue).length)

const sevRank = { critical: 0, high: 1, medium: 2, low: 3 } as Record<string, number>
const columns = [
  { key: 'title', label: 'Incident' },
  { key: 'severity', label: 'Severity', width: '120px', sortValue: (r: any) => sevRank[r.severity]! },
  { key: 'status', label: 'Status', width: '260px' },
  { key: 'ownerName', label: 'Owner', width: '160px' },
  { key: 'actions', label: 'Actions', width: '120px', sortValue: (r: any) => r.actionsOpen },
  { key: 'reportedAt', label: 'Reported', width: '140px' },
]
const actionColumns = [
  { key: 'title', label: 'Action' },
  { key: 'ownerName', label: 'Owner', width: '170px' },
  { key: 'dueAt', label: 'Due', width: '120px', sortValue: (r: any) => new Date(r.dueAt).getTime() },
  { key: 'status', label: 'Status', width: '180px' },
  { key: 'ctl', label: '', width: '190px', sortable: false },
]

const act = useAction()
const completing = ref<number | null>(null)
const evidence = ref('')
async function updateAction(id: number, body: object) {
  if (await act.run(() => api.patch(`/api/actions/${id}`, body), 'Action updated')) { completing.value = null; evidence.value = ''; refreshActions() }
}
</script>
