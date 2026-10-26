<template>
  <div class="page">
    <UiPageHeader title="Alerts" :subtitle="`${counts.all} in view, ${counts.overdue} past SLA`">
      <a :href="`/api/export/alerts`" class="btn"><i class="bi bi-download" aria-hidden="true" /> Export CSV</a>
      <button v-if="allowed('alerts:act')" class="btn btn-primary" type="button" @click="newOpen = true"><i class="bi bi-plus-lg" aria-hidden="true" /> Raise alert</button>
    </UiPageHeader>

    <div class="row-wrap">
      <UiTabs v-model="severity" :tabs="severityTabs" />
      <span class="spacer" />
      <select v-model="view" class="select" style="width:auto" aria-label="Status filter">
        <option value="open">Open</option>
        <option value="all">All (last 30 days)</option>
        <option value="closed">Closed</option>
      </select>
    </div>

    <div v-if="selected.length" class="callout info no-print">
      <i class="bi bi-check2-square" aria-hidden="true" />
      <span>{{ selected.length }} selected</span>
      <span class="spacer" />
      <UiUserSelect v-model="bulkAssignee" style="max-width:280px" />
      <button class="btn btn-sm btn-primary" type="button" :disabled="!bulkAssignee || bulk.pending.value" @click="bulkAssign">Assign</button>
      <button class="btn btn-sm btn-ghost" type="button" @click="selected = []">Clear</button>
    </div>

    <UiPanel flush>
      <UiDataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="filtered"
        clickable
        :selectable="allowed('alerts:act') && view !== 'closed'"
        :active-key="selectedId"
        empty="No alerts match these filters"
        caption="Alerts"
        @row-click="r => selectedId = r.id"
      >
        <template #cell-severity="{ row }"><UiSeverity :value="row.severity" /></template>
        <template #cell-title="{ row }">
          <div class="cell-title">{{ row.title }}</div>
          <div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.ruleId ?? (row.source === 'kri' ? 'KRI' : row.source === 'manual' ? 'Manual' : 'Monitoring') }} · {{ row.module }}</div>
        </template>
        <template #cell-status="{ row }">
          <div class="row" style="gap:6px"><UiPill :value="row.status" /><UiPill v-if="row.overdue" value="overdue" text="SLA breached" icon="bi-clock-history" /></div>
        </template>
        <template #cell-assigneeName="{ row }">
          <div>{{ row.assigneeName ?? 'Unassigned' }}</div>
          <div v-if="row.escalationLevel" class="cell-sub">Level {{ row.escalationLevel + 1 }}: {{ row.currentLevelLabel }}</div>
        </template>
        <template #cell-dueAt="{ row }">
          <span :class="row.overdue ? '' : 'text-2'" :style="row.overdue ? 'color:var(--critical)' : ''">{{ row.status === 'closed' ? fmtDateTime(row.closedAt) : fmtRelative(row.dueAt) }}</span>
          <div class="cell-sub">SLA {{ row.slaLabel }}</div>
        </template>
        <template #cell-detectedAt="{ row }"><span class="mono small">{{ fmtDateTime(row.detectedAt) }}</span></template>
      </UiDataTable>
    </UiPanel>

    <AlertsAlertDrawer :id="selectedId" @close="selectedId = null" @changed="refresh" />
    <AlertsNewAlertModal :open="newOpen" @close="newOpen = false" @created="(id: number) => { newOpen = false; refresh(); selectedId = id }" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'alerts:read' })
useHead({ title: 'Alerts' })

const route = useRoute()
const { can: allowed } = useAccess()
const selectedId = useDrawerQuery('id')
const view = ref<'open' | 'all' | 'closed'>('open')
const severity = ref(typeof route.query.severity === 'string' ? route.query.severity : 'all')
const selected = ref<number[]>([])
const newOpen = ref(false)

const { data: alerts, refresh } = await useFetch('/api/alerts', { query: { view }, default: () => [] })
useLiveRefresh(['alert'], refresh)

const filtered = computed(() => alerts.value.filter(a => severity.value === 'all' || a.severity === severity.value))
const counts = computed(() => ({ all: alerts.value.length, overdue: alerts.value.filter(a => a.overdue).length }))
const severityTabs = computed(() => [
  { key: 'all', label: 'All', count: alerts.value.length },
  ...SEVERITIES.map(s => ({ key: s, label: labelize(s), count: alerts.value.filter(a => a.severity === s).length })),
])

const sevRank = { critical: 0, high: 1, medium: 2, low: 3 } as const
const columns = [
  { key: 'severity', label: 'Severity', width: '120px', sortValue: (r: { severity: Severity }) => sevRank[r.severity] },
  { key: 'title', label: 'Alert' },
  { key: 'status', label: 'Status', width: '210px' },
  { key: 'assigneeName', label: 'Assignee', width: '190px' },
  { key: 'dueAt', label: 'Due', width: '150px', sortValue: (r: { dueAt: string }) => new Date(r.dueAt).getTime() },
  { key: 'detectedAt', label: 'Detected', width: '140px', sortValue: (r: { detectedAt: string }) => new Date(r.detectedAt).getTime() },
]

const bulk = useAction()
const bulkAssignee = ref<number | null>(null)
async function bulkAssign() {
  const res = await bulk.run(() => api.post<{ updated: number }>('/api/alerts/bulk-assign', { ids: selected.value, assigneeId: bulkAssignee.value }))
  if (res) {
    useToast().success(`${plural(res.updated, 'alert')} assigned`)
    selected.value = []
    refresh()
  }
}
</script>
