<template>
  <div class="page">
    <UiPageHeader title="Digital assets intelligence" subtitle="Wallet risk, blockchain exposure, tracing and Travel Rule exceptions">
      <button v-if="allowed('crypto:write')" class="btn btn-primary" type="button" @click="openScreen"><i class="bi bi-search" aria-hidden="true" /> Screen a wallet</button>
    </UiPageHeader>

    <PlannedNotice />

    <div v-if="data" class="kpi-grid">
      <UiKpiCard label="Wallets screened" :value="data.kpis.walletsScreened" tone="purple" :sub="`${data.kpis.blocked} blocked`" />
      <UiKpiCard label="High-risk wallets" :value="data.kpis.highRisk" :tone="data.kpis.highRisk ? 'critical' : 'ok'" sub="Risk score 75 and above" />
      <UiKpiCard label="Transfers to review" :value="data.kpis.transfersOpen" :tone="data.kpis.transfersOpen ? 'high' : 'ok'" :sub="`${data.kpis.crossChain} cross-chain`" />
      <UiKpiCard label="Travel Rule exceptions" :value="data.kpis.travelRuleExceptions" :tone="data.kpis.travelRuleExceptions ? 'high' : 'ok'" sub="Missing or unverified information" />
    </div>

    <UiTabs v-model="tab" :tabs="[{ key: 'transfers', label: 'Transfers and tracing', count: transfers.length }, { key: 'wallets', label: 'Wallets', count: wallets.length }, { key: 'travel', label: 'Travel Rule', count: travel.length }, { key: 'exposure', label: 'Exposure' }]" />

    <template v-if="tab === 'transfers'">
      <UiPanel flush>
        <UiDataTable :columns="transferColumns" :rows="transfers" clickable :active-key="transferId" @row-click="r => transferId = r.id">
          <template #cell-hash="{ row }"><div class="mono small">{{ short(row.hash) }}</div><div class="cell-sub">{{ row.chain }} · {{ fmtRelative(row.at) }}</div></template>
          <template #cell-amount="{ row }"><div class="num small">{{ row.amount }} {{ row.asset }}</div><div class="cell-sub">{{ fmtMoney(row.valueUsd, 'USD') }} {{ row.direction === 'in' ? 'received' : 'sent' }}</div></template>
          <template #cell-customer="{ row }"><div class="small">{{ row.customer?.name }}</div><div class="cell-sub">{{ row.entityName }}</div></template>
          <template #cell-counterparty="{ row }"><div class="small">{{ COUNTERPARTY[row.counterpartyType] }}</div><div class="cell-sub mono">{{ row.counterpartyWallet ? short(row.counterpartyWallet.address) : '' }}</div></template>
          <template #cell-riskScore="{ row }"><AmlScore :score="row.riskScore" /></template>
          <template #cell-status="{ row }"><UiPill :value="row.status === 'open' ? 'review' : row.status" :text="labelize(row.status)" /></template>
        </UiDataTable>
      </UiPanel>

      <UiPanel v-if="selectedTransfer" :title="`Trace: ${selectedTransfer.amount} ${selectedTransfer.asset} on ${selectedTransfer.chain}`" :sub="selectedTransfer.crossChain ?? 'Single chain'">
        <ol class="trace" aria-label="Path of funds">
          <li v-for="(p, i) in selectedTransfer.path" :key="i" :class="riskClass(p.category)">
            <span class="small" style="font-weight:500">{{ p.label }}</span>
            <span class="small muted">{{ CATEGORY[p.category] ?? labelize(p.category) }}</span>
          </li>
        </ol>
        <p class="small muted" style="margin:10px 0 0">Path supplied by the blockchain analytics provider. OysterChecks keeps the risk score, decision, evidence and any SAR or STR.</p>
        <div v-if="selectedTransfer.status === 'open' && allowed('crypto:write')" class="row" style="gap:8px;margin-top:12px">
          <button class="btn" type="button" @click="dialog = 'clear'">Clear transfer</button>
          <button class="btn btn-primary" type="button" @click="dialog = 'escalate'">Escalate to case</button>
        </div>
        <div v-else-if="selectedTransfer.caseRef" class="small" style="margin-top:12px">Escalated to <NuxtLink :to="`/cases?case=${selectedTransfer.caseId}`">{{ selectedTransfer.caseRef }}</NuxtLink></div>
      </UiPanel>
    </template>

    <UiPanel v-if="tab === 'wallets'" flush>
      <UiDataTable :columns="walletColumns" :rows="wallets">
        <template #cell-address="{ row }"><div class="mono small" :title="row.address">{{ short(row.address) }}</div><div class="cell-sub">{{ row.chain }} · {{ row.asset }}</div></template>
        <template #cell-label="{ row }"><div class="small">{{ row.label }}</div><div class="cell-sub">{{ row.customer ? `${row.customer.name} ${row.customer.ref}` : 'Not a customer' }}</div></template>
        <template #cell-exposures="{ row }"><div class="row-wrap" style="gap:4px"><UiPill v-for="e in row.exposures" :key="e.category" :value="e.category === 'exchange' ? 'neutral' : row.riskScore >= 75 ? 'critical' : 'high'" :text="`${CATEGORY[e.category]} ${e.percent}%${e.kind === 'direct' ? '' : ' (indirect)'}`" /></div></template>
        <template #cell-riskScore="{ row }"><AmlScore :score="row.riskScore" /></template>
        <template #cell-status="{ row }"><UiPill :value="row.status" /></template>
        <template #cell-screenedAt="{ row }"><span class="small">{{ fmtRelative(row.screenedAt) }}</span></template>
      </UiDataTable>
    </UiPanel>

    <UiPanel v-if="tab === 'travel'" flush title="Travel Rule" sub="Originator and beneficiary information must travel with each transfer; counterparty VASPs need due diligence">
      <UiDataTable :columns="travelColumns" :rows="travel">
        <template #cell-transfer="{ row }"><div class="num small">{{ row.amount }} {{ row.asset }} {{ row.direction === 'in' ? 'in' : 'out' }}</div><div class="cell-sub">{{ fmtMoney(row.valueUsd, 'USD') }} · {{ fmtRelative(row.at) }}</div></template>
        <template #cell-vasp="{ row }"><div class="small">{{ row.counterpartyVasp ?? 'Unhosted wallet' }}</div><div class="cell-sub">{{ row.counterpartyJurisdiction ?? '' }}<template v-if="row.vaspDueDiligence !== 'n/a'"> · due diligence {{ labelize(row.vaspDueDiligence).toLowerCase() }}</template></div></template>
        <template #cell-info="{ row }"><span class="small"><i :class="['bi', row.originatorComplete ? 'bi-check-circle' : 'bi-x-circle']" :aria-label="row.originatorComplete ? 'complete' : 'missing'" /> Originator · <i :class="['bi', row.beneficiaryComplete ? 'bi-check-circle' : 'bi-x-circle']" :aria-label="row.beneficiaryComplete ? 'complete' : 'missing'" /> Beneficiary</span></template>
        <template #cell-issue="{ row }"><div class="small">{{ row.issue }}</div><div v-if="row.note" class="cell-sub">{{ row.note }}</div></template>
        <template #cell-status="{ row }"><UiPill :value="row.status" /></template>
        <template #cell-actions="{ row }">
          <div v-if="allowed('crypto:write') && (row.status === 'exception' || row.status === 'pending_info')" class="row" style="gap:4px;justify-content:flex-end;flex-wrap:wrap">
            <button v-if="row.status === 'exception'" class="btn btn-sm" type="button" @click="openTravel(row, 'request-info')">Request info</button>
            <button v-if="row.vaspDueDiligence === 'pending' || row.vaspDueDiligence === 'not_started'" class="btn btn-sm" type="button" @click="openTravel(row, 'complete-vasp-dd')">VASP due diligence</button>
            <button class="btn btn-sm btn-primary" type="button" @click="openTravel(row, 'resolve')">Release</button>
            <button class="btn btn-sm btn-ghost" type="button" @click="openTravel(row, 'reject')">Reject</button>
          </div>
        </template>
      </UiDataTable>
    </UiPanel>

    <UiPanel v-if="tab === 'exposure' && data" title="Exposure by category" sub="Wallets with any exposure to each high-risk category, and the largest share seen">
      <ChartsBarList :rows="data.exposure.map((e: any) => ({ label: CATEGORY[e.category] ?? labelize(e.category), value: e.wallets, sub: `up to ${e.maxPercent}%` }))" label="Wallets by exposure category" />
    </UiPanel>

    <UiModal :open="dialog === 'escalate'" title="Escalate transfer to a case" submit-label="Open case" :pending="act.pending.value" @close="dialog = null" @submit="transferAction('escalate')">
      <p class="small text-2">The case score is built from the counterparty wallet's exposure, so every point is explained.</p>
      <UiField label="Reason" required><textarea v-model="note" class="textarea" /></UiField>
    </UiModal>
    <UiModal :open="dialog === 'clear'" title="Clear transfer" submit-label="Clear" :pending="act.pending.value" @close="dialog = null" @submit="transferAction('clear')">
      <UiField label="Rationale" required><textarea v-model="note" class="textarea" /></UiField>
    </UiModal>
    <UiModal :open="dialog === 'travel'" :title="TRAVEL_ACTION[travelAction] ?? ''" :submit-label="TRAVEL_ACTION[travelAction] ?? ''" :pending="act.pending.value" @close="dialog = null" @submit="travelSubmit">
      <UiField label="Note" required><textarea v-model="note" class="textarea" /></UiField>
    </UiModal>

    <UiModal :open="dialog === 'screen'" title="Screen a wallet" :submit-label="screenResult ? 'Done' : 'Screen'" :pending="act.pending.value" @close="dialog = null" @submit="screenResult ? (dialog = null) : screen()">
      <template v-if="!screenResult">
        <UiField label="Wallet address" required><input v-model="screenForm.address" class="input mono" placeholder="bc1q…, 0x…, T…"></UiField>
        <UiField label="Blockchain" required><select v-model="screenForm.chain" class="select"><option v-for="c in CHAINS" :key="c" :value="c">{{ c }}</option></select></UiField>
      </template>
      <template v-else>
        <div class="row" style="gap:10px"><AmlScore :score="screenResult.riskScore" /><UiPill :value="screenResult.status" /></div>
        <div class="row-wrap" style="gap:4px"><UiPill v-for="e in screenResult.exposures" :key="e.category" :value="e.category === 'exchange' ? 'neutral' : 'high'" :text="`${CATEGORY[e.category]} ${e.percent}%`" /></div>
        <p class="small muted" style="margin:0">Illustrative result. No blockchain analytics provider is connected in this demonstration.</p>
      </template>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'crypto:read' })
useHead({ title: 'Digital assets intelligence' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/crypto/overview')
useLiveRefresh(['data'], refresh, 800)

const CATEGORY: Record<string, string> = {
  sanctioned: 'Sanctioned', mixer: 'Mixer', darknet: 'Darknet market', ransomware: 'Ransomware', scam: 'Scam', stolen_funds: 'Stolen funds',
  high_risk_service: 'High-risk service', gambling: 'Gambling', exchange: 'Exchange', customer: 'Customer', unknown: 'Unattributed',
}
const COUNTERPARTY: Record<string, string> = { vasp: 'Hosted (VASP)', unhosted: 'Unhosted wallet', unknown: 'Unknown' }
const TRAVEL_ACTION: Record<string, string> = { 'request-info': 'Request information', 'complete-vasp-dd': 'Record VASP due diligence', resolve: 'Release transfer', reject: 'Reject transfer' }
const CHAINS = ['Bitcoin', 'Ethereum', 'Tron', 'Polygon', 'Solana']
const short = (s: string) => (s.length > 16 ? `${s.slice(0, 8)}…${s.slice(-6)}` : s)
const riskClass = (c: string) => (['sanctioned', 'darknet', 'ransomware', 'stolen_funds'].includes(c) ? 'bad' : ['mixer', 'scam', 'high_risk_service', 'gambling'].includes(c) ? 'warn' : c === 'customer' ? 'own' : '')

const tab = ref('transfers')
const transfers = computed<any[]>(() => data.value?.transfers ?? [])
const wallets = computed<any[]>(() => data.value?.wallets ?? [])
const travel = computed<any[]>(() => data.value?.travelRule ?? [])
const transferId = ref<number | null>(null)
const selectedTransfer = computed(() => transfers.value.find(t => t.id === transferId.value) ?? null)
watch(transfers, (list) => { if (transferId.value === null && list.length) transferId.value = list[0].id }, { immediate: true })

const transferColumns = [
  { key: 'hash', label: 'Transaction', width: '170px' },
  { key: 'amount', label: 'Amount', width: '150px', sortValue: (r: any) => r.valueUsd },
  { key: 'customer', label: 'Customer', sortValue: (r: any) => r.customer?.name },
  { key: 'counterparty', label: 'Counterparty', width: '170px' },
  { key: 'riskScore', label: 'Risk', width: '140px' },
  { key: 'status', label: 'Status', width: '110px' },
]
const walletColumns = [
  { key: 'address', label: 'Wallet', width: '180px' },
  { key: 'label', label: 'Owner or use' },
  { key: 'exposures', label: 'Exposure', sortable: false },
  { key: 'riskScore', label: 'Risk', width: '140px' },
  { key: 'status', label: 'Status', width: '100px' },
  { key: 'screenedAt', label: 'Screened', width: '120px' },
]
const travelColumns = [
  { key: 'transfer', label: 'Transfer', width: '150px' },
  { key: 'vasp', label: 'Counterparty', width: '200px' },
  { key: 'info', label: 'Information', width: '210px', sortable: false },
  { key: 'issue', label: 'Issue' },
  { key: 'status', label: 'Status', width: '120px' },
  { key: 'actions', label: '', width: '230px', sortable: false },
]

const act = useAction()
const dialog = ref<string | null>(null)
const note = ref('')
watch(dialog, () => { note.value = '' })

async function transferAction(action: 'escalate' | 'clear') {
  const res = await act.run(() => api.post<any>(`/api/crypto/transfers/${transferId.value}/${action}`, { note: note.value }), action === 'clear' ? 'Transfer cleared' : '')
  if (res) {
    if (res.caseRef) useToast().success(`Case ${res.caseRef} opened`)
    dialog.value = null
    refresh()
  }
}

const travelRow = ref<any>(null)
const travelAction = ref('request-info')
function openTravel(row: any, action: string) { travelRow.value = row; travelAction.value = action; dialog.value = 'travel' }
async function travelSubmit() {
  if (await act.run(() => api.post(`/api/crypto/travel-rule/${travelRow.value.id}/${travelAction.value}`, { note: note.value }), 'Travel Rule record updated')) { dialog.value = null; refresh() }
}

const screenForm = reactive({ address: '', chain: 'Ethereum' })
const screenResult = ref<any>(null)
function openScreen() { Object.assign(screenForm, { address: '', chain: 'Ethereum' }); screenResult.value = null; dialog.value = 'screen' }
async function screen() {
  const res = await act.run(() => api.post<any>('/api/crypto/wallets/screen', { ...screenForm }))
  if (res) { screenResult.value = res; refresh() }
}
</script>

<style scoped>
.trace { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; align-items: stretch; }
.trace li { display: flex; flex-direction: column; gap: 2px; padding: 8px 12px; border: 1px solid var(--border); border-radius: var(--r-md); background: var(--surface-2); position: relative; }
.trace li + li { margin-left: 18px; }
.trace li + li::before { content: '→'; position: absolute; left: -18px; top: 50%; transform: translateY(-50%); color: var(--muted); width: 18px; text-align: center; }
.trace li.bad { border-color: var(--critical); background: var(--critical-soft); }
.trace li.warn { border-color: var(--warning); background: var(--warning-soft); }
.trace li.own { border-color: var(--accent); background: var(--accent-soft); }
</style>
