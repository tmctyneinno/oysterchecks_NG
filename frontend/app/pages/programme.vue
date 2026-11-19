<template>
  <div class="page">
    <UiPageHeader title="Delivery programme" subtitle="Internal view for OysterChecks delivery and client programme governance. Not shown in customer navigation." />

    <div class="callout info">
      <i class="bi bi-flag" aria-hidden="true" />
      <div class="small"><strong>Status: pre-initiation.</strong> Product documentation and planning materials exist, but the integrated programme has not formally started. The six phases below are provisional: their dates, resources and acceptance gates are re-baselined after Gate 4.</div>
    </div>

    <UiPanel title="Governance gates" sub="Each gate needs the previous one approved. Build work stays locked until Gate 4.">
      <ol class="gates">
        <li v-for="g in gates" :key="g.id" :class="g.status">
          <div class="row" style="gap:8px;align-items:flex-start">
            <span class="gate-num num">{{ g.id }}</span>
            <div style="flex:1;min-width:0">
              <div class="row" style="gap:8px"><span style="font-weight:600">{{ g.name }}</span><UiPill :value="g.status" :text="GATE_STATUS[g.status]" /></div>
              <div class="small text-2">{{ g.decision }}</div>
              <div v-if="g.decidedAt" class="small muted">{{ GATE_STATUS[g.status] }} by {{ g.decidedByName }} on {{ fmtDate(g.decidedAt) }}: {{ g.note }}</div>
            </div>
            <button v-if="allowed('programme:write') && (g.status === 'pending_decision' || g.status === 'deferred')" class="btn btn-sm" type="button" @click="deciding = g">Record decision</button>
          </div>
        </li>
      </ol>
    </UiPanel>

    <div class="row"><h2 class="section-title" style="margin:0">Provisional phases</h2><span class="spacer" /><UiPill :value="buildAuthorised ? 'approved' : 'neutral'" :text="buildAuthorised ? 'Build authorised' : 'Locked until Gate 4'" /></div>
    <ol class="stepper" aria-label="Programme phases">
      <li v-for="p in phases" :key="p.id" :class="p.status">
        <span class="step-num num"><i v-if="p.status === 'complete'" class="bi bi-check-lg" aria-label="Complete" /><template v-else>{{ p.id }}</template></span>
        <span class="small" style="font-weight:500">{{ p.name }}</span>
        <span class="small muted">Provisional weeks {{ p.startWeek }} to {{ p.endWeek }}</span>
      </li>
    </ol>

    <div class="phase-grid">
      <UiPanel v-for="p in phases" :key="p.id" :title="`Phase ${p.id}: ${p.name}`" :sub="`Gate: ${p.gate}`">
        <template #actions><UiPill :value="p.status" /></template>
        <div class="stack">
          <p class="small text-2">{{ p.scope }}</p>
          <UiMeter :value="p.progress" label="Progress" :good="100" :warn="50" />
          <ul class="tasks">
            <li v-for="t in p.tasks" :key="t.id">
              <label class="check">
                <input type="checkbox" :checked="t.done" :disabled="!allowed('programme:write') || !buildAuthorised || p.status === 'complete'" @change="toggle(t.id, ($event.target as HTMLInputElement).checked)">
                <span :class="{ done: t.done }">{{ t.title }}</span>
              </label>
            </li>
          </ul>
          <div v-if="p.signedOffAt" class="small muted">Signed off {{ fmtDate(p.signedOffAt) }}</div>
          <button v-else-if="allowed('programme:write') && buildAuthorised" class="btn btn-sm" type="button" :disabled="p.progress < 100" @click="signoff(p.id)">Sign off phase</button>
        </div>
      </UiPanel>
    </div>

    <UiPanel title="Commercial MVP journeys" sub="The five end-to-end journeys the first release must prove (Version 1.2, section 8)" flush>
      <UiDataTable :columns="[{ key: 'journey', label: 'Journey', width: '240px' }, { key: 'outcome', label: 'Outcome' }, { key: 'where', label: 'Shown in the demo', width: '200px' }]" :rows="journeys" row-key="journey" />
    </UiPanel>

    <UiPanel title="Success criteria" flush>
      <UiDataTable :columns="[{ key: 'criterion', label: 'Criterion' }, { key: 'measure', label: 'Measure' }, { key: 'target', label: 'Target' }]" :rows="criteria" row-key="criterion" />
    </UiPanel>

    <UiModal :open="!!deciding" :title="deciding ? `Gate ${deciding.id}: ${deciding.name}` : ''" submit-label="Record decision" :pending="act.pending.value" @close="deciding = null" @submit="decide">
      <p v-if="deciding" class="small text-2">{{ deciding.decision }}</p>
      <UiField label="Decision" required>
        <div class="row"><label class="check"><input v-model="decision.value" type="radio" value="approved"> Approve</label><label class="check"><input v-model="decision.value" type="radio" value="deferred"> Defer</label></div>
      </UiField>
      <UiField label="Rationale" hint="Recorded in the audit log." required><textarea v-model="decision.note" class="textarea" /></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
import { MVP_JOURNEYS } from '~/demo/extended'

definePageMeta({ permission: 'programme:read' })
useHead({ title: 'Delivery programme' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/programme', { default: () => [] })
const { data: gateData, refresh: refreshGates } = await useFetch('/api/programme/gates', { default: () => [] })
const phases = computed(() => data.value)
const gates = computed(() => gateData.value)
const buildAuthorised = computed(() => gates.value.find(g => g.id === 4)?.status === 'approved')
const journeys = MVP_JOURNEYS
const GATE_STATUS: Record<string, string> = { not_started: 'Not started', pending_decision: 'Decision pending', approved: 'Approved', deferred: 'Deferred' }

const act = useAction()
async function toggle(id: number, done: boolean) {
  await act.run(() => api.patch(`/api/programme/tasks/${id}`, { done }))
  refresh()
}
async function signoff(id: number) {
  if (await act.run(() => api.post(`/api/programme/phases/${id}/signoff`), 'Phase signed off')) refresh()
}

const deciding = ref<any>(null)
const decision = reactive({ value: 'approved', note: '' })
watch(deciding, () => Object.assign(decision, { value: 'approved', note: '' }))
async function decide() {
  if (await act.run(() => api.post(`/api/programme/gates/${deciding.value.id}/decision`, { decision: decision.value, note: decision.note }), 'Gate decision recorded')) {
    deciding.value = null
    refreshGates()
  }
}

const criteria = [
  { criterion: 'Control monitoring coverage', measure: '% of identified key controls monitored by CCM', target: '100% within 60 days of go-live' },
  { criterion: 'Real-time alert latency', measure: 'Time from control failure event to alert generation', target: '< 5 minutes' },
  { criterion: 'Regulatory reporting automation', measure: '% of regulatory reports generated automatically', target: '> 90%' },
  { criterion: 'Audit evidence availability', measure: 'Evidence packages available on demand', target: '100%, no manual compilation' },
  { criterion: 'User adoption', measure: '% of target users actively using the platform', target: '> 85% within 90 days' },
  { criterion: 'False positive rate (AML/CCM)', measure: '% of alerts requiring no action', target: '< 15% within 90 days post-tuning' },
  { criterion: 'Data quality score', measure: 'Accuracy of data ingested from source systems', target: '> 99% validated at SIT' },
]
</script>

<style scoped>
.gates { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.gate-num { width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center; border: 2px solid var(--border-strong); font-size: 12px; font-weight: 600; flex-shrink: 0; }
.pending_decision .gate-num { border-color: var(--warning); color: var(--warning-text); }
.approved .gate-num { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
.stepper { list-style: none; margin: 0; padding: 16px 20px; display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); }
@media (max-width: 900px) { .stepper { grid-template-columns: repeat(3, minmax(0, 1fr)); row-gap: 16px; } }
.stepper li { display: flex; flex-direction: column; gap: 2px; position: relative; padding-right: 12px; }
.stepper li::after { content: ''; position: absolute; top: 12px; left: 32px; right: 8px; height: 2px; background: var(--border); }
.stepper li:last-child::after { display: none; }
.stepper li.complete::after { background: var(--accent); }
.step-num { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; background: var(--surface); border: 2px solid var(--border-strong); font-size: 12px; font-weight: 600; margin-bottom: 6px; position: relative; z-index: 1; }
.in_progress .step-num { border-color: var(--accent); color: var(--accent); }
.complete .step-num { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
.phase-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; }
.tasks { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.done { text-decoration: line-through; color: var(--muted); }
</style>
