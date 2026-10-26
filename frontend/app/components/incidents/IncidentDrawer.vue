<template>
  <UiDrawer :open="!!id" :title="i?.title ?? 'Incident'" :eyebrow="i ? `${i.ref}, ${labelize(i.type)}, ${i.businessUnit}` : ''" :loading="pending && !i" @close="emit('close')">
    <template v-if="i" #meta><UiSeverity :value="i.severity" /><UiPill :value="i.status" /></template>
    <template v-if="i">
      <p v-if="i.description" class="text-2">{{ i.description }}</p>

      <div v-if="i.notifiable" class="callout" :class="i.notifiedAt ? 'info' : 'warn'">
        <i class="bi bi-megaphone" aria-hidden="true" />
        <div class="small">
          <strong>Notify {{ i.regulator }}</strong>
          <div v-if="i.notifiedAt">Notified {{ fmtDateTime(i.notifiedAt) }}</div>
          <div v-else>Due {{ fmtDateTime(i.notificationDueAt) }} ({{ fmtRelative(i.notificationDueAt) }}). {{ i.notificationRule?.basis }}</div>
        </div>
      </div>

      <dl class="kv">
        <dt>Occurred</dt><dd class="mono">{{ fmtDateTime(i.occurredAt) }}</dd>
        <dt>Reported</dt><dd>{{ fmtDateTime(i.reportedAt) }} by {{ i.reportedByName }}</dd>
        <dt>Owner</dt><dd>{{ i.ownerName ?? 'Unassigned' }}</dd>
        <dt>Root cause</dt><dd>{{ i.rootCause ?? 'Not yet determined' }}<span v-if="i.rcaMethod" class="muted"> ({{ i.rcaMethod === '5_whys' ? '5 Whys' : 'Ishikawa' }})</span></dd>
        <template v-if="i.lessonsLearned"><dt>Lessons learned</dt><dd>{{ i.lessonsLearned }}</dd></template>
      </dl>

      <section>
        <div class="section-title">Linked records</div>
        <div class="chip-list">
          <NuxtLink v-for="r in i.risks" :key="`r${r.id}`" :to="`/risks?id=${r.id}`" class="chip">Risk {{ r.ref }}</NuxtLink>
          <NuxtLink v-for="c in i.controls" :key="`c${c.id}`" :to="`/controls?id=${c.id}`" class="chip">Control {{ c.ref }}</NuxtLink>
          <NuxtLink v-for="o in i.obligations" :key="`o${o.id}`" :to="`/compliance?id=${o.id}`" class="chip">Obligation {{ o.ref }}</NuxtLink>
          <span v-if="!i.risks.length && !i.controls.length && !i.obligations.length" class="small muted">None linked yet</span>
        </div>
      </section>

      <section>
        <div class="row"><div class="section-title" style="margin:0">Corrective actions</div><span class="spacer" /><button v-if="canEdit" class="btn btn-sm" type="button" @click="dialog = 'action'">Add action</button></div>
        <div v-for="a in i.actions" :key="a.id" class="row small" style="gap:8px;padding:6px 0;border-bottom:1px solid var(--border)">
          <span class="mono muted">{{ a.ref }}</span><span style="flex:1;min-width:0">{{ a.title }}</span><span class="muted">{{ a.ownerName }} · {{ fmtDate(a.dueAt) }}</span><UiPill :value="a.status" />
        </div>
        <p v-if="!i.actions.length" class="small muted">No actions yet.</p>
      </section>

      <UiEvidence entity-type="incident" :entity-id="i.id" :can-upload="canEdit" />
    </template>
    <template v-if="i && i.status !== 'closed' && canEdit" #footer>
      <button class="btn" type="button" @click="dialog = 'triage'">Triage &amp; RCA</button>
      <button class="btn" type="button" @click="dialog = 'link'">Link records</button>
      <button v-if="!i.notifiable" class="btn btn-ghost" type="button" @click="patch({ notifiable: true })">Mark notifiable</button>
      <button v-else-if="!i.notifiedAt" class="btn" type="button" @click="patch({ notified: true })">Confirm regulator notified</button>
      <span class="spacer" />
      <button v-if="roleDef?.scope !== 'bu'" class="btn btn-primary" type="button" @click="dialog = 'close'">Close incident</button>
    </template>
  </UiDrawer>

  <UiModal :open="dialog === 'triage'" title="Triage & root cause" :pending="act.pending.value" @close="dialog = null" @submit="patch({ severity: f.severity, type: f.type, status: f.status, ownerId: f.ownerId, rcaMethod: f.rcaMethod, rootCause: f.rootCause || null })">
    <div class="form-grid">
      <UiField label="Severity"><select v-model="f.severity" class="select"><option v-for="s in SEVERITIES" :key="s" :value="s">{{ labelize(s) }}</option></select></UiField>
      <UiField label="Type"><select v-model="f.type" class="select"><option v-for="t in ['operational', 'data', 'financial_crime', 'regulatory', 'audit_finding', 'cyber']" :key="t" :value="t">{{ labelize(t) }}</option></select></UiField>
      <UiField label="Status"><select v-model="f.status" class="select"><option v-for="s in ['open', 'investigating', 'remediating']" :key="s" :value="s">{{ labelize(s) }}</option></select></UiField>
      <UiField label="Owner"><UiUserSelect v-model="f.ownerId" /></UiField>
      <UiField label="RCA method"><select v-model="f.rcaMethod" class="select"><option :value="null">None</option><option value="5_whys">5 Whys</option><option value="ishikawa">Ishikawa</option></select></UiField>
    </div>
    <UiField label="Root cause"><textarea v-model="f.rootCause" class="textarea" rows="4" /></UiField>
  </UiModal>

  <UiModal :open="dialog === 'link'" title="Link to risk, control, obligation" :pending="act.pending.value" @close="dialog = null" @submit="patch({ riskIds: f.riskIds, controlIds: f.controlIds })">
    <UiField v-if="allowed('risk:read')" label="Risks"><select v-model="f.riskIds" class="select" multiple size="5"><option v-for="r in risks" :key="r.id" :value="r.id">{{ r.ref }} {{ r.title }}</option></select></UiField>
    <UiField label="Controls that failed"><select v-model="f.controlIds" class="select" multiple size="5"><option v-for="c in controls" :key="c.id" :value="c.id">{{ c.ref }} {{ c.name }}</option></select></UiField>
  </UiModal>

  <UiModal :open="dialog === 'action'" title="Add corrective action" :pending="act.pending.value" @close="dialog = null" @submit="addAction">
    <UiField label="Action" required><input v-model="f.actionTitle" class="input"></UiField>
    <div class="form-grid">
      <UiField label="Owner" required><UiUserSelect v-model="f.actionOwner" /></UiField>
      <UiField label="Due" required><input v-model="f.actionDue" type="date" class="input"></UiField>
    </div>
  </UiModal>

  <UiModal :open="dialog === 'close'" title="Close incident" submit-label="Close" :pending="act.pending.value" @close="dialog = null" @submit="closeIncident">
    <p class="small text-2">You can close an incident once the root cause is recorded, all actions are done and any regulator has been notified.</p>
    <UiField label="Lessons learned" required><textarea v-model="f.lessons" class="textarea" rows="4" /></UiField>
  </UiModal>
</template>

<script setup lang="ts">
const props = defineProps<{ id: number | null }>()
const emit = defineEmits<{ close: [], changed: [] }>()
const { can: allowed, roleDef } = useAccess()
const canEdit = computed(() => allowed('incidents:write'))
const { data: i, pending, refresh } = useFetch(() => `/api/incidents/${props.id}`, { immediate: false, watch: false })
watch(() => props.id, id => { if (id) refresh() }, { immediate: true })
const { data: controls } = useFetch('/api/controls', { default: () => [], key: 'controls-lookup', server: false, immediate: allowed('controls:read') })
const { data: risks } = useFetch('/api/risks', { default: () => [], key: 'risks-lookup', server: false, immediate: allowed('risk:read') })

const dialog = ref<string | null>(null)
const f = reactive({ severity: 'medium', type: 'operational', status: 'open', ownerId: null as number | null, rcaMethod: null as string | null, rootCause: '', riskIds: [] as number[], controlIds: [] as number[], actionTitle: '', actionOwner: null as number | null, actionDue: '', lessons: '' })
watch(dialog, () => {
  const x = i.value
  if (!x) return
  Object.assign(f, { severity: x.severity, type: x.type, status: x.status === 'closed' ? 'open' : x.status, ownerId: x.ownerId, rcaMethod: x.rcaMethod, rootCause: x.rootCause ?? '', riskIds: x.risks.map(r => r.id), controlIds: x.controls.map(c => c.id), actionTitle: '', actionOwner: x.ownerId, actionDue: '', lessons: '' })
})

const act = useAction()
const done = async () => { dialog.value = null; await refresh(); emit('changed') }
async function patch(body: object) {
  if (await act.run(() => api.patch(`/api/incidents/${props.id}`, body), 'Incident updated')) await done()
}
async function addAction() {
  if (await act.run(() => api.post('/api/actions', { title: f.actionTitle, sourceType: 'incident', sourceId: props.id, ownerId: f.actionOwner, dueAt: f.actionDue }), 'Action assigned')) await done()
}
async function closeIncident() {
  if (await act.run(() => api.post(`/api/incidents/${props.id}/close`, { lessonsLearned: f.lessons }), 'Incident closed')) await done()
}
</script>
