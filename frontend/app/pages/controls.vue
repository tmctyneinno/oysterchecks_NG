<template>
  <div class="page">
    <UiPageHeader title="Controls" :subtitle="`${controls.length} controls. Effectiveness is based on test results.`">
      <a href="/api/export/controls" class="btn"><i class="bi bi-download" aria-hidden="true" /> Export</a>
      <button v-if="allowed('controls:write')" class="btn btn-primary" type="button" @click="formOpen = 'new'"><i class="bi bi-plus-lg" aria-hidden="true" /> New control</button>
    </UiPageHeader>

    <div class="kpi-grid">
      <UiKpiCard v-for="e in effectivenessKpis" :key="e.key" :label="e.label" :value="e.count" :tone="toneFor(e.key)" />
      <UiKpiCard label="Under CCM" :value="controls.filter(c => c.ccmRuleId).length" :suffix="`/${controls.length}`" tone="medium" />
    </div>

    <div class="row-wrap">
      <UiTabs v-model="filter" :tabs="filterTabs" />
      <span class="spacer" />
      <input v-model="q" class="input" style="max-width:260px" placeholder="Filter controls" aria-label="Filter controls">
    </div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="visible" clickable :active-key="selectedId" @row-click="r => selectedId = r.id">
        <template #cell-name="{ row }">
          <div class="cell-title">{{ row.name }}</div>
          <div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.businessUnit }} · {{ row.ownerName }}</div>
        </template>
        <template #cell-type="{ row }"><span class="small">{{ labelize(row.type) }} · {{ labelize(row.frequency) }}</span><div class="cell-sub">{{ row.automated ? 'Automated' : 'Manual' }}{{ row.keyControl ? ' · Key' : '' }}</div></template>
        <template #cell-effectiveness="{ row }"><UiPill :value="row.effectiveness" /></template>
        <template #cell-tests="{ row }">
          <span class="test-strip" :aria-label="`Recent tests: ${row.recentTests.map(t => t.result).join(', ') || 'none'}`">
            <span v-for="t in [...row.recentTests].reverse()" :key="t.id" :class="['tick', t.result]" :title="`${fmtDate(t.at)} ${t.result} (${t.method})`" />
          </span>
          <span v-if="row.passRate !== null" class="num small muted" style="margin-left:6px">{{ row.passRate }}%</span>
        </template>
        <template #cell-rule="{ row }">
          <span v-if="row.rule" class="row small" style="gap:6px"><UiLed :state="row.rule.state" /><span class="mono">{{ row.ccmRuleId }}</span></span>
          <span v-else class="small muted">Not monitored</span>
        </template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!selected" :title="selected?.name ?? ''" :eyebrow="selected ? `${selected.ref} · ${selected.businessUnit}` : ''" @close="selectedId = null">
      <template v-if="selected" #meta>
        <UiPill :value="selected.effectiveness" />
        <UiPill v-if="selected.keyControl" tone="medium" text="Key control" />
        <UiPill tone="neutral" :text="selected.automated ? 'Automated' : 'Manual'" />
      </template>
      <template v-if="selected">
        <p class="text-2">{{ selected.description }}</p>
        <dl class="kv">
          <dt>Owner</dt><dd>{{ selected.ownerName }}</dd>
          <dt>Type / frequency</dt><dd>{{ labelize(selected.type) }} · {{ labelize(selected.frequency) }}</dd>
          <dt>CCM rule</dt><dd><span v-if="selected.rule" class="row" style="gap:6px"><UiLed :state="selected.rule.state" /> {{ selected.ccmRuleId }} {{ selected.rule.name }}</span><span v-else class="muted">Not under continuous monitoring</span></dd>
          <dt>Mitigates</dt><dd>{{ plural(selected.riskIds.length, 'risk') }} <NuxtLink v-for="id in selected.riskIds" :key="id" :to="`/risks?id=${id}`" class="small" style="margin-left:6px">view</NuxtLink></dd>
          <dt>Assurance</dt><dd>{{ plural(selected.assuranceCount, 'activity', 'activities') }} across the three lines</dd>
          <dt>Last tested</dt><dd>{{ fmtDateTime(selected.lastTestedAt) }}</dd>
        </dl>
        <section>
          <div class="section-title">Test history</div>
          <div v-for="t in selected.recentTests" :key="t.id" class="row small" style="gap:8px;padding:5px 0;border-bottom:1px solid var(--border)">
            <UiPill :value="t.result" :text="labelize(t.result)" />
            <span class="mono muted">{{ fmtDateTime(t.at) }}</span>
            <span class="muted">{{ t.method }}</span>
            <span class="text-2" style="flex:1;min-width:0">{{ t.notes }}</span>
          </div>
          <p v-if="!selected.recentTests.length" class="small muted">No tests recorded.</p>
        </section>
        <UiEvidence entity-type="control" :entity-id="selected.id" :can-upload="allowed('controls:write')" />
      </template>
      <template v-if="selected && allowed('controls:write')" #footer>
        <button class="btn btn-primary" type="button" @click="testOpen = true">Record test result</button>
        <button class="btn" type="button" @click="formOpen = selected.id">Edit</button>
      </template>
    </UiDrawer>

    <UiModal :open="testOpen" title="Record independent test" submit-label="Record result" :pending="act.pending.value" @close="testOpen = false" @submit="recordTest">
      <UiField label="Result" required>
        <div class="row"><label class="check"><input v-model="test.result" type="radio" value="pass"> Pass</label><label class="check"><input v-model="test.result" type="radio" value="fail"> Fail</label></div>
      </UiField>
      <UiField label="Test notes / sample" required><textarea v-model="test.notes" class="textarea" placeholder="e.g. 25 samples tested, 0 exceptions" /></UiField>
    </UiModal>

    <ControlsControlForm :open="!!formOpen" :control="formOpen === 'new' ? null : controls.find(c => c.id === formOpen) ?? null" @close="formOpen = null" @saved="formOpen = null; refresh()" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'controls:read' })
useHead({ title: 'Controls' })
const { can: allowed } = useAccess()

const { data, refresh } = await useFetch('/api/controls', { default: () => [] })
const controls = computed(() => data.value)
useLiveRefresh(['ccm'], refresh, 3000)

const selectedId = useDrawerQuery('id')
const selected = computed(() => controls.value.find(c => c.id === selectedId.value) ?? null)
const filter = ref('all')
const q = ref('')
const EFF = ['effective', 'partially_effective', 'ineffective', 'not_tested'] as const
const effectivenessKpis = computed(() => EFF.map(key => ({ key, label: labelize(key), count: controls.value.filter(c => c.effectiveness === key).length })))
const filterTabs = computed(() => [{ key: 'all', label: 'All', count: controls.value.length }, ...effectivenessKpis.value.map(e => ({ key: e.key, label: e.label, count: e.count }))])
const visible = computed(() => controls.value.filter(c => (filter.value === 'all' || c.effectiveness === filter.value) && (!q.value || `${c.ref} ${c.name} ${c.businessUnit}`.toLowerCase().includes(q.value.toLowerCase()))))

const columns = [
  { key: 'name', label: 'Control' },
  { key: 'type', label: 'Type', width: '170px' },
  { key: 'effectiveness', label: 'Effectiveness', width: '170px' },
  { key: 'tests', label: 'Recent tests', width: '180px', sortable: false },
  { key: 'rule', label: 'CCM', width: '120px', sortValue: (r: any) => r.ccmRuleId ?? '' },
]

const act = useAction()
const testOpen = ref(false)
const test = reactive({ result: 'pass', notes: '' })
async function recordTest() {
  const res = await act.run(() => api.post<{ effectiveness: string }>(`/api/controls/${selected.value!.id}/tests`, test))
  if (res) {
    useToast().success(`Test recorded. Control is now ${labelize(res.effectiveness).toLowerCase()}.`)
    testOpen.value = false
    test.notes = ''
    refresh()
  }
}
const formOpen = ref<number | 'new' | null>(null)
</script>

<style scoped>
.test-strip { display: inline-flex; gap: 3px; vertical-align: middle; }
.tick { width: 8px; height: 16px; border-radius: 2px; }
.tick.pass { background: var(--accent); }
.tick.fail { background: var(--critical); }
</style>
