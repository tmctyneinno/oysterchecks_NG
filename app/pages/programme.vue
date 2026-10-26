<template>
  <div class="page">
    <UiPageHeader title="Implementation" subtitle="Six phases over 26 weeks" />

    <ol class="stepper" aria-label="Programme phases">
      <li v-for="p in phases" :key="p.id" :class="p.status">
        <span class="step-num num"><i v-if="p.status === 'complete'" class="bi bi-check-lg" aria-label="Complete" /><template v-else>{{ p.id }}</template></span>
        <span class="small" style="font-weight:500">{{ p.name }}</span>
        <span class="small muted">Weeks {{ p.startWeek }} to {{ p.endWeek }}</span>
      </li>
    </ol>

    <div class="phase-grid">
      <UiPanel v-for="p in phases" :key="p.id" :title="`Phase ${p.id}: ${p.name}`" :sub="`Weeks ${p.startWeek} to ${p.endWeek}. Gate: ${p.gate}`">
        <template #actions><UiPill :value="p.status" /></template>
        <div class="stack">
          <p class="small text-2">{{ p.scope }}</p>
          <UiMeter :value="p.progress" label="Progress" :good="100" :warn="50" />
          <ul class="tasks">
            <li v-for="t in p.tasks" :key="t.id">
              <label class="check">
                <input type="checkbox" :checked="t.done" :disabled="!allowed('programme:write') || p.status === 'complete'" @change="toggle(t.id, ($event.target as HTMLInputElement).checked)">
                <span :class="{ done: t.done }">{{ t.title }}</span>
              </label>
            </li>
          </ul>
          <div v-if="p.signedOffAt" class="small muted">Signed off {{ fmtDate(p.signedOffAt) }}</div>
          <button v-else-if="allowed('programme:write')" class="btn btn-sm" type="button" :disabled="p.progress < 100" @click="signoff(p.id)">Sign off phase</button>
        </div>
      </UiPanel>
    </div>

    <UiPanel title="Success criteria" flush>
      <UiDataTable :columns="[{ key: 'criterion', label: 'Criterion' }, { key: 'measure', label: 'Measure' }, { key: 'target', label: 'Target' }]" :rows="criteria" row-key="criterion" />
    </UiPanel>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'programme:read' })
useHead({ title: 'Implementation' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/programme', { default: () => [] })
const phases = computed(() => data.value)
const act = useAction()
async function toggle(id: number, done: boolean) {
  if (await act.run(() => api.patch(`/api/programme/tasks/${id}`, { done }))) refresh()
  else refresh()
}
async function signoff(id: number) {
  if (await act.run(() => api.post(`/api/programme/phases/${id}/signoff`), 'Phase signed off')) refresh()
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
