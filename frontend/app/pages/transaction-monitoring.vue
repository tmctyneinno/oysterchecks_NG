<template>
  <div class="page">
    <UiPageHeader title="Transaction monitoring" subtitle="Bank, card, mobile money, remittance, wallet and crypto activity in one alert queue">
      <NuxtLink to="/aml?tab=held" class="btn"><i class="bi bi-pause-circle" aria-hidden="true" /> Held payments</NuxtLink>
      <NuxtLink to="/cases" class="btn"><i class="bi bi-search" aria-hidden="true" /> Cases</NuxtLink>
    </UiPageHeader>

    <PlannedNotice />

    <div v-if="data" class="kpi-grid">
      <UiKpiCard label="Open alerts" :value="data.kpis.open" :tone="data.kpis.open ? 'high' : 'ok'" :sub="`${data.kpis.unassigned} not yet assigned`" />
      <UiKpiCard label="High-score alerts" :value="data.kpis.highScore" :tone="data.kpis.highScore ? 'critical' : 'ok'" sub="Score 75 and above" />
      <UiKpiCard label="Escalated to cases" :value="data.kpis.escalated" tone="purple" to="/cases" sub="Investigated in Cases" />
      <UiKpiCard label="False-positive rate" :value="data.kpis.falsePositiveRate" suffix="%" :tone="data.kpis.falsePositiveRate > 15 ? 'high' : 'ok'" sub="Last 30 days, target below 15%" />
      <UiKpiCard label="Transactions (24h)" :value="fmtCompact(data.kpis.transactions24h)" :sub="`${data.channels.length} channels`" />
      <UiKpiCard label="Scenarios" :value="data.kpis.scenariosActive" suffix="active" :sub="`${data.kpis.scenariosTesting} in testing`" />
    </div>

    <UiTabs v-model="tab" :tabs="[{ key: 'alerts', label: 'Alert queue', count: openAlerts.length }, { key: 'scenarios', label: 'Scenarios', count: data?.scenarios.length }, { key: 'channels', label: 'Channels', count: data?.channels.length }]" />

    <template v-if="tab === 'alerts'">
      <div class="row-wrap">
        <span class="small muted">Activity</span>
        <button v-for="f in FILTERS" :key="f.key" type="button" class="btn btn-sm" :class="{ 'btn-primary': filter === f.key }" @click="filter = f.key">{{ f.label }}</button>
        <span class="spacer" />
        <label class="check small"><input v-model="showClosed" type="checkbox"> Show closed and escalated</label>
      </div>
      <UiPanel flush>
        <UiDataTable :columns="alertColumns" :rows="visibleAlerts" clickable :active-key="alertId" empty="No alerts in this view" @row-click="r => alertId = r.id">
          <template #cell-ref="{ row }"><div class="mono">{{ row.ref }}</div><div class="cell-sub">{{ fmtRelative(row.at) }}</div></template>
          <template #cell-scenario="{ row }"><div class="cell-title">{{ row.scenarioName }}</div><div class="cell-sub"><span class="mono">{{ row.scenarioId }}</span> · {{ row.channelName }}</div></template>
          <template #cell-customer="{ row }"><div class="small">{{ row.customer?.name ?? 'Unknown' }}</div><div class="cell-sub">{{ row.entityName }}</div></template>
          <template #cell-amount="{ row }"><span class="num small">{{ fmtMoney(row.amount, row.currency) }}</span></template>
          <template #cell-score="{ row }"><AmlScore :score="row.score" /></template>
          <template #cell-status="{ row }"><UiPill :value="row.status" /></template>
          <template #cell-assigneeName="{ row }"><span class="small">{{ row.assigneeName ?? 'Unassigned' }}</span></template>
        </UiDataTable>
      </UiPanel>
    </template>

    <UiPanel v-if="tab === 'scenarios' && data" flush title="Scenario performance" sub="Rules and behavioural analytics. Changes to thresholds need a reason and are audited.">
      <UiDataTable :columns="scenarioColumns" :rows="data.scenarios">
        <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub"><span class="mono">{{ row.id }}</span> · {{ labelize(row.kind) }} · {{ row.channelNames.join(', ') }}<template v-if="row.linkedRule"> · feeds {{ row.linkedRule }}</template></div></template>
        <template #cell-threshold="{ row }"><span class="small">{{ row.threshold }}</span></template>
        <template #cell-status="{ row }"><UiPill :value="row.status" /></template>
        <template #cell-alerts30d="{ row }"><span class="num">{{ row.alerts30d }}</span></template>
        <template #cell-falsePositiveRate="{ row }"><span class="num" :class="{ warn: row.falsePositiveRate > 15 }">{{ row.falsePositiveRate }}%</span></template>
        <template #cell-caseConversion="{ row }"><span class="num">{{ row.caseConversion }}%</span></template>
        <template #cell-lastTunedAt="{ row }"><span class="small">{{ fmtRelative(row.lastTunedAt) }}</span></template>
        <template #cell-actions="{ row }"><button v-if="allowed('tm:write')" class="btn btn-sm btn-ghost" type="button" @click="openTune(row)">Tune</button></template>
      </UiDataTable>
    </UiPanel>

    <UiPanel v-if="tab === 'channels' && data" flush title="Monitored channels" sub="Each channel feeds the same scenarios, customer risk and case model">
      <UiDataTable :columns="channelColumns" :rows="data.channels" row-key="key">
        <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub">{{ row.source }}</div></template>
        <template #cell-mode="{ row }"><span class="small">{{ labelize(row.mode) }}</span><div class="cell-sub">{{ row.latency }}</div></template>
        <template #cell-entities="{ row }"><span class="small">{{ row.entities.join(', ') }}</span></template>
        <template #cell-volume24h="{ row }"><span class="num">{{ fmtNumber(row.volume24h) }}</span></template>
        <template #cell-alerts24h="{ row }"><span class="num">{{ row.alerts24h }}</span></template>
        <template #cell-status="{ row }"><UiPill :value="row.status === 'planned' ? 'neutral' : 'ok'" :text="labelize(row.status)" /></template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!alertId" :title="a?.scenarioName ?? ''" :eyebrow="a ? `${a.ref} · ${a.channelName} · ${a.entityName}` : ''" :loading="pending && !a" @close="alertId = null">
      <template v-if="a" #meta><UiPill :value="a.status" /><AmlScore :score="a.score" /></template>
      <template v-if="a">
        <p class="small">{{ a.summary }}</p>
        <section>
          <div class="section-title">Customer behaviour</div>
          <div class="behaviour">
            <div><div class="small muted">Usual pattern</div><div class="small">{{ a.baseline }}</div></div>
            <i class="bi bi-arrow-right muted" aria-hidden="true" />
            <div><div class="small muted">This alert</div><div class="small" style="font-weight:500">{{ a.observed }}</div></div>
          </div>
        </section>
        <section>
          <div class="section-title">Linked activity</div>
          <table class="table"><tbody>
            <tr v-for="(l, i) in a.linked" :key="i"><td class="small mono" style="width:120px">{{ fmtDateTime(l.at) }}</td><td class="small">{{ l.description }}</td><td class="num small" style="text-align:right">{{ fmtMoney(l.amount, l.currency) }}</td></tr>
          </tbody></table>
        </section>
        <dl class="kv small">
          <dt>Customer</dt><dd>{{ a.customer?.name }} <span class="mono muted">{{ a.customer?.ref }}</span>, {{ a.customer?.country }}, {{ labelize(a.customer?.riskRating) }} risk, KYC {{ labelize(a.customer?.kycStatus) }}</dd>
          <dt>Legal entity</dt><dd>{{ a.entityName }}</dd>
          <dt>Scenario</dt><dd>{{ a.scenario?.id }} {{ a.scenario?.name }}: {{ a.scenario?.threshold }}</dd>
          <dt>Amount</dt><dd class="num">{{ fmtMoney(a.amount, a.currency) }} <span class="muted">({{ fmtGBP(a.amountGbp) }} equivalent)</span></dd>
          <dt>Assignee</dt><dd>{{ a.assigneeName ?? 'Unassigned' }}</dd>
          <dt v-if="a.outcome">Outcome</dt><dd v-if="a.outcome">{{ a.outcome }} <NuxtLink v-if="a.caseId" :to="`/cases?case=${a.caseId}`">Open case</NuxtLink></dd>
        </dl>
        <section v-if="a.history.length">
          <div class="section-title">History</div>
          <ul class="small" style="margin:0;padding-left:18px"><li v-for="(h, i) in a.history" :key="i">{{ h.action }} · {{ h.actorName }} <span class="muted mono">{{ fmtDateTime(h.at) }}</span><span v-if="h.note" class="text-2">: {{ h.note }}</span></li></ul>
        </section>
      </template>
      <template v-if="a && allowed('tm:write') && (a.status === 'new' || a.status === 'in_review')" #footer>
        <button v-if="!a.assigneeId || a.assigneeId !== user?.id" class="btn" type="button" @click="send('assign', {}, 'Assigned to you')">Assign to me</button>
        <button class="btn" type="button" @click="dialog = 'close'">Close, no case</button>
        <button class="btn btn-primary" type="button" @click="dialog = 'escalate'">Escalate to case</button>
      </template>
    </UiDrawer>

    <UiModal :open="dialog === 'close'" title="Close without a case" submit-label="Close alert" :pending="act.pending.value" @close="dialog = null" @submit="send('close', { rationale: form.text, falsePositive: form.falsePositive }, 'Alert closed')">
      <label class="check small"><input v-model="form.falsePositive" type="checkbox"> False positive (feeds scenario tuning)</label>
      <UiField label="Rationale" hint="Recorded in the audit log and used to tune the scenario." required><textarea v-model="form.text" class="textarea" /></UiField>
    </UiModal>

    <UiModal :open="dialog === 'escalate'" title="Escalate to a case" submit-label="Open case" :pending="act.pending.value" @close="dialog = null" @submit="escalate">
      <p class="small text-2">A case opens with a risk score that shows every contributing factor. The MLRO is notified.</p>
      <UiField label="Reason for escalation" required><textarea v-model="form.text" class="textarea" /></UiField>
    </UiModal>

    <UiModal :open="dialog === 'tune'" :title="`Tune ${tuning?.id ?? ''}`" submit-label="Save change" :pending="act.pending.value" @close="dialog = null" @submit="saveTune">
      <UiField label="Threshold" required><input v-model="tune.threshold" class="input"></UiField>
      <UiField label="Status" required><select v-model="tune.status" class="select"><option value="active">Active</option><option value="testing">Testing</option></select></UiField>
      <UiField label="Reason for the change" hint="Recorded in the audit log (model governance)." required><textarea v-model="tune.reason" class="textarea" /></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'tm:read' })
useHead({ title: 'Transaction monitoring' })
const { can: allowed, user } = useAccess()
const route = useRoute()

const { data, refresh } = await useFetch('/api/tm/overview')
useLiveRefresh(['data'], refresh, 800)

const FILTERS = [{ key: 'all', label: 'All' }, { key: 'fiat', label: 'Fiat' }, { key: 'crypto', label: 'Crypto' }]
const filter = ref('all')
const showClosed = ref(false)
const tab = ref(['scenarios', 'channels'].includes(String(route.query.tab)) ? String(route.query.tab) : 'alerts')
const alerts = computed<any[]>(() => data.value?.alerts ?? [])
const openAlerts = computed(() => alerts.value.filter(a => a.status === 'new' || a.status === 'in_review'))
const visibleAlerts = computed(() => (showClosed.value ? alerts.value : openAlerts.value)
  .filter(a => filter.value === 'all' || (filter.value === 'crypto' ? a.channel === 'crypto' : a.channel !== 'crypto')))

const alertColumns = [
  { key: 'ref', label: 'Alert', width: '120px' },
  { key: 'scenario', label: 'Scenario', sortValue: (r: any) => r.scenarioName },
  { key: 'customer', label: 'Customer', sortValue: (r: any) => r.customer?.name },
  { key: 'amount', label: 'Amount', width: '150px', align: 'right' as const, sortValue: (r: any) => r.amountGbp },
  { key: 'score', label: 'Score', width: '140px' },
  { key: 'status', label: 'Status', width: '120px' },
  { key: 'assigneeName', label: 'Assignee', width: '140px' },
]
const scenarioColumns = [
  { key: 'name', label: 'Scenario' },
  { key: 'threshold', label: 'Threshold', width: '230px', sortable: false },
  { key: 'status', label: 'Status', width: '100px' },
  { key: 'alerts30d', label: 'Alerts (30d)', width: '100px', align: 'right' as const },
  { key: 'falsePositiveRate', label: 'False positives', width: '120px', align: 'right' as const },
  { key: 'caseConversion', label: 'To case', width: '90px', align: 'right' as const },
  { key: 'lastTunedAt', label: 'Last tuned', width: '120px' },
  { key: 'actions', label: '', width: '70px', sortable: false },
]
const channelColumns = [
  { key: 'name', label: 'Channel' },
  { key: 'mode', label: 'Mode', width: '150px' },
  { key: 'entities', label: 'Legal entities', sortable: false },
  { key: 'volume24h', label: 'Volume (24h)', width: '120px', align: 'right' as const },
  { key: 'alerts24h', label: 'Alerts (24h)', width: '100px', align: 'right' as const },
  { key: 'status', label: 'Status', width: '110px' },
]

const alertId = useDrawerQuery('id')
const { data: detail, pending, refresh: refreshDetail } = useFetch(() => `/api/tm/alerts/${alertId.value}`, { immediate: false, watch: false })
watch(alertId, (v) => { if (v) refreshDetail() }, { immediate: true })
const a = computed(() => (alertId.value && detail.value?.id === alertId.value ? detail.value : null))

const dialog = ref<string | null>(null)
const form = reactive({ text: '', falsePositive: true })
watch(dialog, () => Object.assign(form, { text: '', falsePositive: true }))
const act = useAction()
async function send(action: string, body: object, message: string) {
  const res = await act.run(() => api.post<any>(`/api/tm/alerts/${alertId.value}/${action}`, body), message)
  if (res) { dialog.value = null; await Promise.all([refresh(), refreshDetail()]) }
  return res
}
async function escalate() {
  const res = await send('escalate', { note: form.text }, '')
  if (res?.caseRef) useToast().success(`Case ${res.caseRef} opened`)
}

const tuning = ref<any>(null)
const tune = reactive({ threshold: '', status: 'active', reason: '' })
function openTune(row: any) { tuning.value = row; Object.assign(tune, { threshold: row.threshold, status: row.status, reason: '' }); dialog.value = 'tune' }
async function saveTune() {
  if (await act.run(() => api.patch(`/api/tm/scenarios/${tuning.value.id}`, { ...tune }), 'Scenario updated')) { dialog.value = null; refresh() }
}
</script>

<style scoped>
.behaviour { display: grid; grid-template-columns: 1fr auto 1fr; gap: 12px; align-items: center; padding: 10px 12px; background: var(--surface-2); border-radius: var(--r-md); }
.warn { color: var(--warning-text); font-weight: 600; }
</style>
