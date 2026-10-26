<template>
  <div class="page">
    <UiPageHeader title="Control monitoring" :subtitle="data ? `${data.rules.length} rules, last run ${fmtRelative(lastRun)}` : ''">
      <a href="/api/export/ccm" class="btn"><i class="bi bi-download" aria-hidden="true" /> Export register</a>
      <template v-if="allowed('ccm:manage')">
        <button class="btn" type="button" :disabled="run.pending.value" @click="runNow"><i class="bi bi-arrow-repeat" aria-hidden="true" /> Run now</button>
        <button class="btn btn-primary" type="button" @click="editing = 'new'"><i class="bi bi-plus-lg" aria-hidden="true" /> New rule</button>
      </template>
    </UiPageHeader>

    <div v-if="data" class="kpi-grid">
      <UiKpiCard label="Passing" :value="count('pass')" tone="ok" sub="Evaluating clean" />
      <UiKpiCard label="Near threshold" :value="count('warn')" tone="high" sub="Items approaching threshold" />
      <UiKpiCard label="Failing" :value="count('fail')" tone="critical" sub="Alerts raised" />
      <UiKpiCard label="Open alerts from rules" :value="data.rules.reduce((s, r) => s + r.openAlerts, 0)" tone="medium" to="/alerts" sub="Across all rules" />
    </div>

    <UiTabs v-model="filter" :tabs="tabs" />

    <div class="rule-grid">
      <article v-for="r in visible" :key="r.id" class="rule panel">
        <div class="row" style="align-items:flex-start">
          <div style="min-width:0">
            <div class="small muted"><span class="mono">{{ r.id }}</span> · {{ r.module }}</div>
            <h2 class="rule-name">{{ r.name }}</h2>
          </div>
          <span class="spacer" />
          <UiPill :value="!r.enabled ? 'neutral' : r.state" :text="!r.enabled ? 'Disabled' : STATE_LABEL[r.state]" />
        </div>
        <p class="small text-2">{{ r.description }}</p>
        <div class="row-wrap">
          <UiSeverity :value="r.severity" />
          <span class="small muted">Resolve within {{ r.slaLabel.toLowerCase() }}</span>
        </div>
        <dl class="kv small" style="grid-template-columns:90px 1fr">
          <dt>Escalation</dt><dd>{{ r.escalationLabel }}</dd>
          <dt>Source</dt><dd>{{ r.dataSource }}</dd>
          <dt>Last run</dt><dd>{{ r.lastBreaches }} breaches, {{ r.lastWarnings }} near threshold</dd>
        </dl>
        <div class="row" style="margin-top:auto">
          <NuxtLink v-if="r.openAlerts" :to="`/alerts`" class="small">View {{ r.openAlerts }} open alert{{ r.openAlerts === 1 ? '' : 's' }}</NuxtLink>
          <span class="spacer" />
          <button v-if="allowed('ccm:manage')" class="btn btn-sm" type="button" @click="editing = r.id">Configure</button>
        </div>
      </article>
    </div>

    <CcmRuleEditor v-if="data" :rule="editingRule" :is-new="editing === 'new'" :catalogue="data.catalogue" :targets="data.targets" :open="!!editing" @close="editing = null" @saved="editing = null; refresh()" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'ccm:read' })
useHead({ title: 'Control monitoring' })
const { can: allowed } = useAccess()

const { data, refresh } = await useFetch('/api/ccm/rules')
useLiveRefresh(['ccm'], refresh)

const filter = ref('all')
const STATE_LABEL = { pass: 'Passing', warn: 'Near threshold', fail: 'Failing', unknown: 'Not run' } as Record<string, string>
const count = (s: string) => data.value?.rules.filter(r => r.enabled && r.state === s).length ?? 0
const tabs = computed(() => [
  { key: 'all', label: 'All', count: data.value?.rules.length },
  { key: 'fail', label: 'Failing', count: count('fail') },
  { key: 'warn', label: 'Near threshold', count: count('warn') },
  { key: 'pass', label: 'Passing', count: count('pass') },
])
const visible = computed(() => (data.value?.rules ?? []).filter(r => filter.value === 'all' || r.state === filter.value))
const lastRun = computed(() => data.value?.rules.map(r => r.lastRunAt).filter(Boolean).sort().at(-1) ?? null)

const editing = ref<string | null>(null)
const editingRule = computed(() => data.value?.rules.find(r => r.id === editing.value) ?? null)

const run = useAction()
async function runNow() {
  const res = await run.run(() => api.post<{ alertsCreated: number, escalated: number, durationMs: number }>('/api/ccm/run'))
  if (res) {
    useToast().success(`Run complete. ${res.alertsCreated} new alerts, ${res.escalated} escalations.`)
    refresh()
  }
}
</script>

<style scoped>
.rule-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px; }
.rule { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.rule-name { font-size: 15px; font-weight: 600; margin-top: 2px; }
</style>
