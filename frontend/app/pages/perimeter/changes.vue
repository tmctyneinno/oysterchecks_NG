<template>
  <div class="page">
    <UiPageHeader title="Regulatory change" subtitle="From a new rule to the obligations, controls and actions it affects, with maker-checker approval">
      <NuxtLink to="/perimeter" class="btn"><i class="bi bi-globe2" aria-hidden="true" /> Coverage and sources</NuxtLink>
    </UiPageHeader>

    <ol class="flow small" aria-label="Regulatory change workflow">
      <li v-for="(s, i) in FLOW" :key="s"><span class="num step">{{ i + 1 }}</span>{{ s }}</li>
    </ol>

    <UiTabs v-model="view" :tabs="views.map(v => ({ key: v.key, label: v.label, count: rows.filter(v.match).length }))" />

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="visible" clickable :active-key="selectedId" empty="Nothing in this view" @row-click="r => selectedId = r.id">
        <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.issuer }} · <span class="mono">{{ row.jurisdiction }}</span></div></template>
        <template #cell-priority="{ row }"><UiSeverity :value="row.priority" /></template>
        <template #cell-status="{ row }"><UiPill :value="row.status" :text="STATUS[row.status]" /></template>
        <template #cell-progress="{ row }">
          <div class="small">{{ row.extractedTotal ? `${row.extractedValidated} of ${row.extractedTotal} validated` : 'Nothing extracted yet' }}</div>
          <div class="cell-sub">{{ row.assessedEntities }} of 5 entities assessed, {{ row.applicableEntities }} in scope</div>
        </template>
        <template #cell-effectiveAt="{ row }"><span class="small">{{ row.effectiveAt ? fmtDate(row.effectiveAt) : 'Not set' }}</span><div class="cell-sub">{{ row.effectiveAt ? fmtRelative(row.effectiveAt) : '' }}</div></template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!selectedId" :title="c?.title ?? ''" :eyebrow="c ? `${c.ref} · ${c.issuer}` : ''" :loading="pending && !c" @close="selectedId = null">
      <template v-if="c" #meta>
        <UiPill :value="c.status" :text="STATUS[c.status]" /><UiSeverity :value="c.priority" />
        <span class="small muted">Prepared by {{ c.makerName ?? 'nobody yet' }}<template v-if="c.approverName">, approved by {{ c.approverName }}</template></span>
      </template>
      <template v-if="c">
        <p class="small text-2">{{ c.summary }}</p>

        <section v-if="c.source">
          <div class="section-title">Source and provenance</div>
          <dl class="kv small">
            <dt>Source</dt><dd>{{ c.source.title }} <span class="mono muted">{{ c.source.ref }}</span></dd>
            <dt>Issuer</dt><dd>{{ c.source.issuer }} · {{ c.source.pack }}</dd>
            <dt>Location</dt><dd>{{ c.source.url }} · {{ c.source.version }}</dd>
            <dt>Retrieved</dt><dd>{{ fmtDate(c.source.retrievedAt) }}, last checked {{ fmtRelative(c.source.checkedAt) }}</dd>
            <dt>Fingerprint</dt><dd><code :title="`SHA-256 ${c.source.sha256}`">{{ c.source.sha256.slice(0, 16) }}…</code></dd>
            <dt>Dates</dt><dd>Published {{ fmtDate(c.publishedAt) }}<template v-if="c.effectiveAt">, effective {{ fmtDate(c.effectiveAt) }}</template></dd>
          </dl>
        </section>

        <section>
          <div class="section-title">Extracted requirements</div>
          <p class="small muted" style="margin:0 0 8px">Drafted automatically from the source text. A person must validate each one before the assessment can be submitted.</p>
          <ul v-if="c.extracted.length" class="list">
            <li v-for="x in c.extracted" :key="x.id" class="row" style="align-items:flex-start;gap:10px">
              <UiPill :value="x.validated ? 'ok' : 'high'" :text="x.validated ? 'Validated' : 'Needs validation'" />
              <span class="small" style="flex:1">{{ x.text }}</span>
              <button v-if="c.editable && !x.validated" class="btn btn-sm" type="button" @click="openValidate(x)">Validate</button>
            </li>
          </ul>
          <p v-else class="small muted">No requirements extracted yet. This item is on the horizon only.</p>
        </section>

        <section>
          <div class="section-title">Applicability by legal entity</div>
          <table class="table">
            <tbody>
              <tr v-for="a in c.applicability" :key="a.entityId">
                <td><div class="small" style="font-weight:500">{{ a.entityName }}</div><div class="cell-sub mono">{{ a.jurisdiction }}</div></td>
                <td style="width:170px"><UiPill v-if="a.decision" :value="a.decision" /><span v-else class="small muted">Not assessed</span></td>
                <td><span class="small">{{ a.rationale ?? '' }}</span></td>
                <td style="width:90px;text-align:right"><button v-if="c.editable" class="btn btn-sm btn-ghost" type="button" @click="openApplicability(a)">{{ a.decision ? 'Change' : 'Assess' }}</button></td>
              </tr>
            </tbody>
          </table>
        </section>

        <section>
          <div class="row"><div class="section-title" style="margin:0">Impact</div><span class="spacer" /><button v-if="c.editable" class="btn btn-sm" type="button" @click="openMapping">Edit mapping</button></div>
          <dl class="kv small" style="margin-top:8px">
            <dt>Obligations</dt><dd><UiChipList :items="c.impacts.obligations" to="/compliance?id=" label="title" /></dd>
            <dt>Controls</dt><dd><UiChipList :items="c.impacts.controls" to="/controls?id=" label="name" /></dd>
            <dt>Policies</dt><dd><UiChipList :items="c.impacts.policies" to="/governance?id=" label="title" /></dd>
            <dt>Risks</dt><dd><UiChipList :items="c.impacts.risks" to="/risks?id=" label="title" /></dd>
          </dl>
        </section>

        <section>
          <div class="row"><div class="section-title" style="margin:0">Actions</div><span class="spacer" /><button v-if="c.editable" class="btn btn-sm" type="button" @click="dialog = 'action'">Add action</button></div>
          <ul v-if="c.actions.length" class="list" style="margin-top:8px">
            <li v-for="a in c.actions" :key="a.id" class="row" style="gap:10px">
              <UiPill :value="a.status === 'done' ? 'done' : 'open'" :text="a.status === 'done' ? 'Done' : 'Open'" />
              <span class="small" style="flex:1">{{ a.title }}</span>
              <span class="small muted">{{ a.ownerName }} · due {{ fmtDate(a.dueAt) }}</span>
            </li>
          </ul>
          <p v-else class="small muted" style="margin-top:8px">No actions yet.</p>
        </section>

        <section>
          <div class="section-title">History</div>
          <ol class="change-log">
            <li v-for="(h, i) in [...c.history].reverse()" :key="i">
              <div class="small"><span style="font-weight:500">{{ h.action }}</span> · {{ h.actorName }} <span class="mono muted">{{ fmtDateTime(h.at) }}</span></div>
              <div v-if="h.note" class="small text-2">{{ h.note }}</div>
            </li>
          </ol>
        </section>
      </template>
      <template v-if="c" #footer>
        <template v-if="c.editable">
          <button v-if="c.status === 'new' || c.status === 'triage'" class="btn" type="button" @click="dialog = 'triage'">Triage</button>
          <button class="btn btn-primary" type="button" :disabled="act.pending.value" @click="send('submit', {}, 'Submitted for approval')">Submit for approval</button>
        </template>
        <template v-else-if="c.canApprove">
          <button class="btn" type="button" @click="dialog = 'return'">Return to preparer</button>
          <button class="btn btn-primary" type="button" @click="dialog = 'approve'">Approve</button>
        </template>
        <span v-else-if="c.status === 'pending_approval' && c.isMaker" class="small muted">You prepared this assessment, so a different person must approve it.</span>
        <span v-else-if="c.status === 'pending_approval'" class="small muted">Waiting for approval by the CCO.</span>
      </template>
    </UiDrawer>

    <UiModal :open="dialog === 'validate'" title="Validate extracted requirement" submit-label="Confirm as validated" :pending="act.pending.value" @close="dialog = null" @submit="send('validate', { extractId: form.extractId, text: form.text }, 'Requirement validated')">
      <UiField label="Requirement" hint="Correct the wording if the extraction is not accurate." required><textarea v-model="form.text" class="textarea" /></UiField>
    </UiModal>

    <UiModal :open="dialog === 'applicability'" :title="`Applicability: ${form.entityName}`" submit-label="Record decision" :pending="act.pending.value" @close="dialog = null" @submit="send('applicability', { entityId: form.entityId, decision: form.decision, rationale: form.text }, 'Applicability recorded')">
      <UiField label="Decision" required>
        <div class="stack" style="gap:6px">
          <label v-for="d in ['applicable', 'partially_applicable', 'not_applicable']" :key="d" class="check"><input v-model="form.decision" type="radio" :value="d"> {{ labelize(d) }}</label>
        </div>
      </UiField>
      <UiField label="Rationale" hint="Which activities, products or locations bring the entity into scope, or why not." required><textarea v-model="form.text" class="textarea" /></UiField>
    </UiModal>

    <UiModal :open="dialog === 'mapping'" title="Map the impact" submit-label="Save mapping" :pending="act.pending.value" @close="dialog = null" @submit="send('map', mapping, 'Impact mapping saved')">
      <div class="map-grid">
        <fieldset v-for="g in mapGroups" :key="g.key">
          <legend class="small" style="font-weight:600">{{ g.label }}</legend>
          <label v-for="o in g.options" :key="o.id" class="check small"><input v-model="mapping[g.key]" type="checkbox" :value="o.id"> <span><span class="mono">{{ o.ref }}</span> {{ o.title }}</span></label>
        </fieldset>
      </div>
    </UiModal>

    <UiModal :open="dialog === 'action'" title="Add action" submit-label="Assign action" :pending="act.pending.value" @close="dialog = null" @submit="send('add-action', { title: form.text, ownerId: form.ownerId, dueAt: form.dueAt }, 'Action assigned')">
      <UiField label="Action" required><input v-model="form.text" class="input"></UiField>
      <UiField label="Owner" required><UiUserSelect v-model="form.ownerId" /></UiField>
      <UiField label="Due date" required><input v-model="form.dueAt" type="date" class="input"></UiField>
    </UiModal>

    <UiModal :open="dialog === 'triage'" title="Triage" submit-label="Save priority" :pending="act.pending.value" @close="dialog = null" @submit="send('triage', { priority: form.priority }, 'Change triaged')">
      <UiField label="Priority" required>
        <div class="row"><label v-for="p in ['high', 'medium', 'low']" :key="p" class="check"><input v-model="form.priority" type="radio" :value="p"> {{ labelize(p) }}</label></div>
      </UiField>
    </UiModal>

    <UiModal :open="dialog === 'approve'" title="Approve assessment" submit-label="Approve" :pending="act.pending.value" @close="dialog = null" @submit="send('approve', { note: form.text || undefined }, 'Assessment approved')">
      <UiField label="Note" hint="Optional"><textarea v-model="form.text" class="textarea" /></UiField>
    </UiModal>

    <UiModal :open="dialog === 'return'" title="Return to preparer" submit-label="Return" :pending="act.pending.value" @close="dialog = null" @submit="send('return', { note: form.text }, 'Returned to preparer')">
      <UiField label="What needs to change" required><textarea v-model="form.text" class="textarea" /></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'perimeter:read' })
useHead({ title: 'Regulatory change' })

const FLOW = ['Capture', 'Validate extraction', 'Assess applicability', 'Map impact and actions', 'Submit', 'Approve (different person)']
const STATUS: Record<string, string> = { new: 'New', triage: 'In triage', applicability: 'Assessing', pending_approval: 'Awaiting approval', approved: 'Approved', returned: 'Returned' }

const { data: list, refresh: refreshList } = await useFetch('/api/perimeter/changes', { default: () => [] })
const rows = computed(() => list.value)
const views = [
  { key: 'open', label: 'Open', match: (r: any) => !['approved', 'pending_approval'].includes(r.status) },
  { key: 'approval', label: 'Awaiting approval', match: (r: any) => r.status === 'pending_approval' },
  { key: 'approved', label: 'Approved', match: (r: any) => r.status === 'approved' },
  { key: 'all', label: 'All', match: () => true },
]
const view = ref('all')
const visible = computed(() => rows.value.filter(views.find(v => v.key === view.value)!.match))
const columns = [
  { key: 'title', label: 'Change' },
  { key: 'priority', label: 'Priority', width: '110px' },
  { key: 'status', label: 'Status', width: '160px' },
  { key: 'progress', label: 'Progress', width: '230px', sortable: false },
  { key: 'effectiveAt', label: 'Effective', width: '140px' },
]

const selectedId = useDrawerQuery('id')
const { data: detail, pending, refresh: refreshDetail } = useFetch(() => `/api/perimeter/changes/${selectedId.value}`, { immediate: false, watch: false })
watch(selectedId, (v) => { if (v) refreshDetail() }, { immediate: true })
const c = computed(() => (selectedId.value && detail.value?.id === selectedId.value ? detail.value : null))
useLiveRefresh(['data', 'activity'], () => Promise.all([refreshList(), selectedId.value ? refreshDetail() : null]), 600)

// Lookups for impact mapping (only what the role may read).
const { can: allowed } = useAccess()
const { data: obligations } = useFetch('/api/obligations', { default: () => [], server: false, immediate: allowed('compliance:read') })
const { data: controls } = useFetch('/api/controls', { default: () => [], key: 'controls-lookup', server: false, immediate: allowed('controls:read') })
const { data: policies } = useFetch('/api/policies', { default: () => [], server: false, immediate: allowed('governance:read') })
const { data: risks } = useFetch('/api/risks', { default: () => [], key: 'risks-lookup', server: false, immediate: allowed('risk:read') })
const mapGroups = computed(() => [
  { key: 'obligationIds' as const, label: 'Obligations', options: obligations.value.map(o => ({ id: o.id, ref: o.ref, title: o.title })) },
  { key: 'controlIds' as const, label: 'Controls', options: controls.value.map(o => ({ id: o.id, ref: o.ref, title: o.name })) },
  { key: 'policyIds' as const, label: 'Policies', options: policies.value.map(o => ({ id: o.id, ref: o.ref, title: o.title })) },
  { key: 'riskIds' as const, label: 'Risks', options: risks.value.map(o => ({ id: o.id, ref: o.ref, title: o.title })) },
])

const dialog = ref<string | null>(null)
const form = reactive({ extractId: 0, text: '', entityId: '', entityName: '', decision: 'applicable', ownerId: null as number | null, dueAt: '', priority: 'medium' })
const mapping = reactive({ obligationIds: [] as number[], controlIds: [] as number[], policyIds: [] as number[], riskIds: [] as number[] })
watch(dialog, (d) => {
  if (d === 'action' || d === 'approve' || d === 'return') Object.assign(form, { text: '', ownerId: null, dueAt: '' })
  if (d === 'triage') form.priority = c.value?.priority ?? 'medium'
})
function openValidate(x: { id: number, text: string }) { Object.assign(form, { extractId: x.id, text: x.text }); dialog.value = 'validate' }
function openApplicability(a: { entityId: string, entityName: string, decision: string | null, rationale: string | null }) {
  Object.assign(form, { entityId: a.entityId, entityName: a.entityName, decision: a.decision ?? 'applicable', text: a.rationale ?? '' })
  dialog.value = 'applicability'
}
function openMapping() {
  const ids = c.value!.impactIds
  Object.assign(mapping, { obligationIds: [...ids.obligationIds], controlIds: [...ids.controlIds], policyIds: [...ids.policyIds], riskIds: [...ids.riskIds] })
  dialog.value = 'mapping'
}

const act = useAction()
async function send(action: string, body: object, message: string) {
  if (await act.run(() => api.post(`/api/perimeter/changes/${selectedId.value}/${action}`, body), message)) {
    dialog.value = null
    await Promise.all([refreshDetail(), refreshList()])
  }
}
</script>

<style scoped>
.flow { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px 16px; color: var(--text-2); }
.flow li { display: inline-flex; align-items: center; gap: 6px; }
.step { width: 20px; height: 20px; border-radius: 50%; background: var(--surface-2); border: 1px solid var(--border); display: inline-grid; place-items: center; font-size: 11px; }
.list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.change-log { list-style: none; margin: 0; padding: 0 0 0 12px; border-left: 2px solid var(--border); display: flex; flex-direction: column; gap: 10px; }
.map-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; max-height: 50vh; overflow-y: auto; }
@media (max-width: 640px) { .map-grid { grid-template-columns: minmax(0, 1fr); } }
.map-grid fieldset { border: 1px solid var(--border); border-radius: var(--r-md); padding: 8px 10px; margin: 0; display: flex; flex-direction: column; gap: 6px; min-width: 0; }
</style>
