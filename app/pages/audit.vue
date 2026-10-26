<template>
  <div class="page">
    <UiPageHeader title="Internal audit" :subtitle="data ? `${openFindings} open findings, ${overdueFindings} overdue` : ''">
      <template v-if="allowed('audit:write')">
        <button class="btn" type="button" @click="dialog = 'engagement'"><i class="bi bi-calendar-plus" aria-hidden="true" /> Plan engagement</button>
        <button class="btn btn-primary" type="button" @click="dialog = 'finding'"><i class="bi bi-plus-lg" aria-hidden="true" /> Raise finding</button>
      </template>
    </UiPageHeader>

    <UiTabs v-model="tab" :tabs="[{ key: 'findings', label: 'Findings & recommendations', count: data?.findings.length }, { key: 'engagements', label: 'Audit plan', count: data?.engagements.length }]" />

    <UiPanel v-if="tab === 'findings' && data" flush>
      <UiDataTable :columns="findingColumns" :rows="data.findings" clickable :active-key="selectedId" @row-click="r => selectedId = r.id">
        <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.engagementRef }} · {{ row.businessUnit }}</div></template>
        <template #cell-rating="{ row }"><UiSeverity :value="row.rating" /></template>
        <template #cell-status="{ row }"><div class="row" style="gap:6px"><UiPill :value="row.status" /><UiPill v-if="row.overdue" value="overdue" text="Overdue" /></div></template>
        <template #cell-dueAt="{ row }"><span class="small">{{ fmtDate(row.dueAt) }}</span></template>
      </UiDataTable>
    </UiPanel>

    <UiPanel v-if="tab === 'engagements' && data" flush>
      <UiDataTable :columns="engColumns" :rows="data.engagements">
        <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.businessUnit }} · lead {{ row.leadName }}</div></template>
        <template #cell-period="{ row }"><span class="small">{{ fmtDate(row.plannedStart) }} – {{ fmtDate(row.plannedEnd) }}</span></template>
        <template #cell-status="{ row }">
          <select v-if="allowed('audit:write') && row.status !== 'closed'" class="select" style="width:auto" :value="row.status" :aria-label="`Status of ${row.ref}`" @change="advance(row, ($event.target as HTMLSelectElement).value)">
            <option v-for="s in ['planned', 'fieldwork', 'reporting', 'closed']" :key="s" :value="s">{{ labelize(s) }}</option>
          </select>
          <UiPill v-else :value="row.status" />
        </template>
        <template #cell-rating="{ row }"><UiPill v-if="row.rating" :value="row.rating" /><span v-else class="muted">-</span></template>
        <template #cell-findings="{ row }"><span class="num">{{ row.openFindings }}</span><span class="muted small"> open / {{ row.findings }}</span></template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!selected" :title="selected?.title ?? ''" :eyebrow="selected ? `${selected.ref} · ${selected.engagementRef}` : ''" @close="selectedId = null">
      <template v-if="selected" #meta><UiSeverity :value="selected.rating" /><UiPill :value="selected.status" /><UiPill v-if="selected.overdue" value="overdue" text="Overdue" /></template>
      <template v-if="selected">
        <dl class="kv">
          <dt>Owner</dt><dd>{{ selected.ownerName }}</dd>
          <dt>Business unit</dt><dd>{{ selected.businessUnit }}</dd>
          <dt>Agreed due date</dt><dd>{{ fmtDate(selected.dueAt) }}</dd>
          <dt>Management response</dt><dd>{{ selected.managementResponse ?? 'Awaiting response' }}</dd>
          <template v-if="selected.closedAt"><dt>Closed</dt><dd>{{ fmtDate(selected.closedAt) }}. {{ selected.closureEvidence }}</dd></template>
        </dl>
        <UiEvidence entity-type="audit_finding" :entity-id="selected.id" :can-upload="allowed('audit:write') || selected.ownerId === user?.id" />
      </template>
      <template v-if="selected && selected.status === 'open'" #footer>
        <button v-if="selected.ownerId === user?.id || allowed('audit:write')" class="btn" type="button" @click="dialog = 'response'">Management response</button>
        <button v-if="allowed('audit:write')" class="btn" type="button" @click="dialog = 'due'">Change due date</button>
        <span class="spacer" />
        <button v-if="allowed('audit:write')" class="btn btn-primary" type="button" @click="dialog = 'close'">Validate &amp; close</button>
      </template>
    </UiDrawer>

    <UiModal :open="dialog === 'response'" title="Management response" :pending="act.pending.value" @close="dialog = null" @submit="patchFinding({ managementResponse: text })"><UiField label="Response and action plan" required><textarea v-model="text" class="textarea" rows="5" /></UiField></UiModal>
    <UiModal :open="dialog === 'due'" title="Change agreed due date" :pending="act.pending.value" @close="dialog = null" @submit="patchFinding({ dueAt: text })"><UiField label="New due date" hint="Due-date changes are audit-logged." required><input v-model="text" type="date" class="input"></UiField></UiModal>
    <UiModal :open="dialog === 'close'" title="Validate closure" submit-label="Close finding" :pending="act.pending.value" @close="dialog = null" @submit="patchFinding({ close: { evidence: text } })"><UiField label="Validation evidence" required><textarea v-model="text" class="textarea" rows="4" /></UiField></UiModal>

    <UiModal :open="!!closing" :title="`Close ${closing?.ref ?? ''}`" submit-label="Close engagement" :pending="act.pending.value" @close="closing = null" @submit="closeEngagement">
      <UiField label="Overall audit opinion" required>
        <select v-model="closingRating" class="select"><option v-for="r in ['satisfactory', 'needs_improvement', 'unsatisfactory']" :key="r" :value="r">{{ labelize(r) }}</option></select>
      </UiField>
    </UiModal>

    <UiModal :open="dialog === 'engagement'" title="Plan engagement" :pending="act.pending.value" @close="dialog = null" @submit="createEngagement">
      <UiField label="Title" required><input v-model="eng.title" class="input"></UiField>
      <UiField label="Scope"><textarea v-model="eng.scope" class="textarea" /></UiField>
      <div class="form-grid">
        <UiField label="Business unit" required><select v-model="eng.businessUnit" class="select"><option v-for="b in BUSINESS_UNITS" :key="b">{{ b }}</option></select></UiField>
        <UiField label="Lead" required><UiUserSelect v-model="eng.leadId" :roles="['AUDIT']" /></UiField>
        <UiField label="Start" required><input v-model="eng.plannedStart" type="date" class="input"></UiField>
        <UiField label="End" required><input v-model="eng.plannedEnd" type="date" class="input"></UiField>
      </div>
    </UiModal>

    <UiModal :open="dialog === 'finding'" title="Raise finding" :pending="act.pending.value" @close="dialog = null" @submit="createFinding">
      <UiField label="Engagement" required><select v-model="fnd.engagementId" class="select"><option v-for="e in data?.engagements ?? []" :key="e.id" :value="e.id">{{ e.ref }} {{ e.title }}</option></select></UiField>
      <UiField label="Finding" required><textarea v-model="fnd.title" class="textarea" /></UiField>
      <div class="form-grid">
        <UiField label="Rating"><select v-model="fnd.rating" class="select"><option value="high">High</option><option value="medium">Medium</option><option value="low">Low</option></select></UiField>
        <UiField label="Owner" required><UiUserSelect v-model="fnd.ownerId" /></UiField>
        <UiField label="Agreed due date" required><input v-model="fnd.dueAt" type="date" class="input"></UiField>
      </div>
      <label class="check"><input v-model="fnd.raiseIncident" type="checkbox"> Also log as an issue in Incident Management</label>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'audit:read' })
useHead({ title: 'Internal audit' })
const { can: allowed, user } = useAccess()
const { data, refresh } = await useFetch('/api/audit')
const tab = ref('findings')
const selectedId = useDrawerQuery('id')
const selected = computed(() => data.value?.findings.find(f => f.id === selectedId.value) ?? null)
const openFindings = computed(() => data.value?.findings.filter(f => f.status === 'open').length ?? 0)
const overdueFindings = computed(() => data.value?.findings.filter(f => f.overdue).length ?? 0)

const findingColumns = [
  { key: 'title', label: 'Finding' },
  { key: 'rating', label: 'Rating', width: '120px' },
  { key: 'ownerName', label: 'Owner', width: '170px' },
  { key: 'status', label: 'Status', width: '170px' },
  { key: 'dueAt', label: 'Due', width: '120px', sortValue: (r: any) => new Date(r.dueAt).getTime() },
]
const engColumns = [
  { key: 'title', label: 'Engagement' },
  { key: 'period', label: 'Period', width: '220px', sortable: false },
  { key: 'status', label: 'Status', width: '160px' },
  { key: 'rating', label: 'Rating', width: '160px' },
  { key: 'findings', label: 'Findings', width: '120px' },
]

const dialog = ref<string | null>(null)
const text = ref('')
watch(dialog, () => { text.value = '' })
const act = useAction()
async function patchFinding(body: object) {
  if (await act.run(() => api.patch(`/api/audit/findings/${selected.value!.id}`, body), 'Finding updated')) { dialog.value = null; refresh() }
}
const closing = ref<{ id: number, ref: string } | null>(null)
const closingRating = ref('needs_improvement')
async function advance(row: { id: number, ref: string }, status: string) {
  if (status === 'closed') { closing.value = row; return refresh() } // rating captured in modal
  if (await act.run(() => api.patch(`/api/audit/engagements/${row.id}`, { status }), `${row.ref} moved to ${labelize(status)}`)) refresh()
}
async function closeEngagement() {
  const row = closing.value!
  if (await act.run(() => api.patch(`/api/audit/engagements/${row.id}`, { status: 'closed', rating: closingRating.value }), `${row.ref} closed`)) { closing.value = null; refresh() }
}
const eng = reactive({ title: '', scope: '', businessUnit: BUSINESS_UNITS[0] as string, leadId: null as number | null, plannedStart: '', plannedEnd: '' })
async function createEngagement() {
  if (await act.run(() => api.post('/api/audit/engagements', eng), 'Engagement planned')) { dialog.value = null; refresh() }
}
const fnd = reactive({ engagementId: null as number | null, title: '', rating: 'medium', ownerId: null as number | null, dueAt: '', raiseIncident: false })
async function createFinding() {
  if (await act.run(() => api.post('/api/audit/findings', fnd), 'Finding raised and owner notified')) { dialog.value = null; refresh() }
}
</script>
