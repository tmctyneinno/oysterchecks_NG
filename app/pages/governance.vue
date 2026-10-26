<template>
  <div class="page">
    <UiPageHeader title="Policies" :subtitle="`${policies.length} policies, ${pendingMine} awaiting your acknowledgement`">
      <button v-if="allowed('governance:write')" class="btn btn-primary" type="button" @click="formOpen = 'new'"><i class="bi bi-plus-lg" aria-hidden="true" /> New policy</button>
    </UiPageHeader>

    <div class="kpi-grid">
      <UiKpiCard label="Approved" :value="policies.filter(p => p.status === 'approved').length" tone="ok" />
      <UiKpiCard label="In review" :value="policies.filter(p => p.status === 'in_review').length" tone="high" />
      <UiKpiCard label="Drafts" :value="policies.filter(p => p.status === 'draft').length" tone="medium" />
      <UiKpiCard label="Review overdue" :value="reviewOverdue" :tone="reviewOverdue ? 'critical' : 'ok'" />
    </div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="policies" clickable :active-key="selectedId" @row-click="r => selectedId = r.id">
        <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }} v{{ row.version }}</span> · {{ row.ownerName }} · {{ row.frameworks.join(', ') }}</div></template>
        <template #cell-status="{ row }"><UiPill :value="row.status" /></template>
        <template #cell-ack="{ row }">
          <UiMeter v-if="row.status === 'approved'" :value="row.ackTarget ? (100 * row.ackCount) / row.ackTarget : 0" :display="`${row.ackCount}/${row.ackTarget}`" :good="90" :warn="60" />
          <span v-else class="small muted">-</span>
        </template>
        <template #cell-reviewDate="{ row }"><span class="small" :style="row.reviewOverdue ? 'color:var(--critical)' : ''">{{ fmtDate(row.reviewDate) }}</span><div v-if="row.reviewOverdue" class="cell-sub">Review overdue</div></template>
        <template #cell-mine="{ row }"><UiPill v-if="row.status === 'approved'" :value="row.acknowledgedByMe ? 'done' : 'due_soon'" :text="row.acknowledgedByMe ? 'Acknowledged' : 'Action needed'" /></template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!selected" :title="selected?.title ?? ''" :eyebrow="selected ? `${selected.ref} · version ${selected.version}` : ''" @close="selectedId = null">
      <template v-if="selected" #meta><UiPill :value="selected.status" /><UiPill v-if="selected.reviewOverdue" value="overdue" text="Review overdue" /></template>
      <template v-if="selected">
        <p class="text-2">{{ selected.summary }}</p>
        <dl class="kv">
          <dt>Owner</dt><dd>{{ selected.ownerName }}</dd>
          <dt>Jurisdiction</dt><dd>{{ selected.jurisdiction }}</dd>
          <dt>Frameworks</dt><dd><span class="chip-list"><span v-for="f in selected.frameworks" :key="f" class="chip">{{ f }}</span></span></dd>
          <dt>Approved</dt><dd>{{ selected.approvedAt ? `${fmtDate(selected.approvedAt)} by ${selected.approvedByName}` : 'Not approved' }}</dd>
          <dt>Review date</dt><dd>{{ fmtDate(selected.reviewDate) }}</dd>
          <dt>Traceability</dt><dd>{{ plural(selected.riskIds.length, 'risk') }}, {{ plural(selected.controlIds.length, 'control') }}, {{ plural(selected.obligationIds.length, 'obligation') }}</dd>
          <dt>Acknowledged</dt><dd>{{ selected.ackCount }} of {{ selected.ackTarget }} users (current version)</dd>
        </dl>
        <section>
          <div class="section-title">Version history</div>
          <ul class="timeline">
            <li v-for="h in history" :key="h.id"><span class="dot" aria-hidden="true" /><div class="small"><strong>v{{ h.version }} {{ labelize(h.status) }}</strong> · {{ h.actorName }} <span class="muted mono">{{ fmtDateTime(h.at) }}</span><div v-if="h.note" class="text-2">{{ h.note }}</div></div></li>
          </ul>
        </section>
        <UiEvidence entity-type="policy" :entity-id="selected.id" :can-upload="allowed('governance:write')" />
      </template>
      <template v-if="selected" #footer>
        <button v-if="selected.status === 'approved' && !selected.acknowledgedByMe" class="btn btn-primary" type="button" @click="doAction('acknowledge')">I have read and accept this policy</button>
        <template v-if="allowed('governance:write')">
          <button v-if="selected.status === 'draft'" class="btn btn-primary" type="button" @click="doAction('submit')">Submit for approval</button>
          <button v-if="selected.status !== 'approved' && selected.status !== 'retired'" class="btn" type="button" @click="formOpen = selected.id">Edit</button>
          <button v-if="selected.status === 'approved'" class="btn" type="button" @click="noteAction = 'new-version'">Start new version</button>
          <button v-if="selected.status !== 'retired'" class="btn btn-ghost" type="button" @click="noteAction = 'retire'">Retire</button>
        </template>
        <template v-if="allowed('policy:approve') && selected.status === 'in_review'">
          <span class="spacer" />
          <button class="btn btn-danger" type="button" @click="noteAction = 'reject'">Return to owner</button>
          <button class="btn btn-primary" type="button" @click="doAction('approve')">Approve</button>
        </template>
      </template>
    </UiDrawer>

    <UiModal :open="!!noteAction" :title="noteAction === 'reject' ? 'Return policy to owner' : noteAction === 'retire' ? 'Retire policy' : 'Start new version'" :submit-label="labelize(noteAction ?? '')" :pending="act.pending.value" :danger="noteAction === 'retire'" @close="noteAction = null" @submit="doAction(noteAction!, note)">
      <UiField label="Note" required><textarea v-model="note" class="textarea" /></UiField>
    </UiModal>

    <GovernancePolicyForm :open="!!formOpen" :policy="formOpen === 'new' ? null : policies.find(p => p.id === formOpen) ?? null" @close="formOpen = null" @saved="formOpen = null; refresh()" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'governance:read' })
useHead({ title: 'Policies' })
const { can: allowed } = useAccess()

const { data, refresh } = await useFetch('/api/policies', { default: () => [] })
const policies = computed(() => data.value)
const selectedId = useDrawerQuery('id')
const selected = computed(() => policies.value.find(p => p.id === selectedId.value) ?? null)
const { data: history, refresh: refreshHistory } = useFetch(() => `/api/policies/${selectedId.value}/history`, { default: () => [], immediate: false, watch: false })
watch(selectedId, id => { if (id) refreshHistory() }, { immediate: true })

const reviewOverdue = computed(() => policies.value.filter(p => p.reviewOverdue).length)
const pendingMine = computed(() => policies.value.filter(p => p.status === 'approved' && !p.acknowledgedByMe).length)
const columns = [
  { key: 'title', label: 'Policy' },
  { key: 'status', label: 'Status', width: '120px' },
  { key: 'ack', label: 'Acknowledgement', width: '180px', sortable: false },
  { key: 'reviewDate', label: 'Review date', width: '140px' },
  { key: 'mine', label: 'You', width: '150px', sortable: false },
]

const formOpen = ref<number | 'new' | null>(null)
const noteAction = ref<string | null>(null)
const note = ref('')
const act = useAction()
async function doAction(action: string, n?: string) {
  if (await act.run(() => api.post(`/api/policies/${selected.value!.id}/${action}`, n ? { note: n } : {}), 'Policy updated')) {
    noteAction.value = null
    note.value = ''
    await refresh()
    refreshHistory()
  }
}
</script>
