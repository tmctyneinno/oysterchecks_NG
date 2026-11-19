<template>
  <div class="page">
    <UiPageHeader title="Customers and screening" subtitle="KYC and KYB, sanctions and PEP screening, EDD and held payments">
      <NuxtLink to="/cases" class="btn"><i class="bi bi-search" aria-hidden="true" /> Cases</NuxtLink>
    </UiPageHeader>

    <div v-if="overview" class="kpi-grid">
      <UiKpiCard label="Customers" :value="overview.customers.total" tone="purple" :sub="`${overview.customers.pendingKyc} awaiting KYC`" />
      <UiKpiCard label="Held payments" :value="overview.transactions24h.pendingHighValue" :tone="overview.transactions24h.pendingHighValue ? 'critical' : 'ok'" sub="£10k and over, awaiting screening" />
      <UiKpiCard label="EDD outstanding" :value="overview.customers.eddOutstanding" :tone="overview.customers.eddOutstanding ? 'high' : 'ok'" :sub="`${overview.customers.high} high-risk customers`" />
      <UiKpiCard label="Transactions (24h)" :value="fmtCompact(overview.transactions24h.count)" tone="medium" :sub="fmtGBP(overview.transactions24h.value, true)" />
      <UiKpiCard label="High-risk customers" :value="overview.customers.high" :tone="overview.customers.high ? 'high' : 'ok'" :sub="`${overview.customers.pep} PEP, ${overview.customers.sanctionsReview} in sanctions review`" />
    </div>

    <UiTabs v-model="tab" :tabs="[{ key: 'customers', label: 'Customers', count: customers.length }, { key: 'held', label: 'Held payments', count: held.length }]" />

    <UiPanel v-if="tab === 'held'" flush title="Held payments" sub="High-value transactions without a sanctions screening result">
      <UiDataTable :columns="txColumns" :rows="held" empty="No payments awaiting screening">
        <template #cell-ref="{ row }"><span class="mono">{{ row.ref }}</span><div class="cell-sub">{{ fmtDateTime(row.at) }}</div></template>
        <template #cell-amount="{ row }"><span class="num">{{ fmtGBP(row.amount) }}</span></template>
        <template #cell-customer="{ row }">{{ row.customer.name }} <span class="mono muted small">{{ row.customer.ref }}</span></template>
        <template #cell-counterparty="{ row }">{{ row.counterparty }} <UiPill :value="row.counterpartyRisk === 'high' ? 'critical' : row.counterpartyRisk === 'medium' ? 'high' : 'neutral'" :text="row.counterpartyCountry" /></template>
        <template #cell-actions="{ row }"><button v-if="allowed('aml:write')" class="btn btn-sm btn-primary" type="button" @click="screening = row">Screen</button></template>
      </UiDataTable>
    </UiPanel>

    <UiPanel v-if="tab === 'customers'" flush>
      <UiDataTable :columns="customerColumns" :rows="customers">
        <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ labelize(row.type) }} · {{ row.country }}<span v-if="row.pep"> · PEP</span></div></template>
        <template #cell-riskRating="{ row }"><UiPill :value="row.riskRating === 'high' ? 'critical' : row.riskRating === 'enhanced' ? 'high' : 'neutral'" :text="labelize(row.riskRating)" /></template>
        <template #cell-kyc="{ row }"><UiPill :value="row.kycExpired ? 'overdue' : row.kycStatus" :text="labelize(row.kycExpired ? 'Refresh due' : row.kycStatus)" /><div class="cell-sub">{{ row.kycExpiresAt ? `Expires ${fmtDate(row.kycExpiresAt)}` : '' }}</div></template>
        <template #cell-sanctionsStatus="{ row }"><UiPill :value="row.sanctionsStatus" /></template>
        <template #cell-eddStatus="{ row }"><UiPill :value="row.eddStatus === 'required' ? 'high' : row.eddStatus" :text="labelize(row.eddStatus)" /></template>
        <template #cell-actions="{ row }">
          <div v-if="allowed('aml:write')" class="row" style="gap:4px;justify-content:flex-end">
            <button v-if="row.kycStatus !== 'verified'" class="btn btn-sm" type="button" @click="custAction = { row, action: 'verify-kyc' }">Verify KYC</button>
            <button v-else-if="row.kycExpired" class="btn btn-sm" type="button" @click="custAction = { row, action: 'refresh-kyc' }">Refresh KYC</button>
            <button v-if="row.eddStatus === 'required'" class="btn btn-sm" type="button" @click="quickCustomer(row.id, 'start-edd')">Start EDD</button>
            <button v-if="row.eddStatus === 'in_progress' || row.eddStatus === 'required'" class="btn btn-sm btn-primary" type="button" @click="custAction = { row, action: 'complete-edd' }">Complete EDD</button>
            <button class="btn btn-sm btn-ghost" type="button" @click="custAction = { row, action: 'rescreen' }">Re-screen</button>
          </div>
        </template>
      </UiDataTable>
    </UiPanel>

    <UiModal :open="!!screening" :title="screening ? `Screen ${screening.ref}` : ''" submit-label="Record decision" :pending="act.pending.value" @close="screening = null" @submit="screen">
      <template v-if="screening">
        <p class="small text-2">{{ fmtGBP(screening.amount) }} {{ screening.direction === 'out' ? 'to' : 'from' }} {{ screening.counterparty }} ({{ screening.counterpartyCountry }}) for {{ screening.customer.name }}.</p>
        <UiField label="Screening outcome" required>
          <div class="row"><label class="check"><input v-model="screen_.result" type="radio" value="clear"> Clear, release payment</label><label class="check"><input v-model="screen_.result" type="radio" value="hit"> Hit, hold and open a case</label></div>
        </UiField>
        <UiField label="Rationale" required><textarea v-model="screen_.note" class="textarea" /></UiField>
      </template>
    </UiModal>

    <UiModal :open="!!custAction" :title="custAction ? `${labelize(custAction.action)}: ${custAction.row.ref}` : ''" :pending="act.pending.value" @close="custAction = null" @submit="submitCustomer">
      <template v-if="custAction?.action === 'complete-edd'">
        <UiField label="Source of funds / wealth evidence" required><textarea v-model="cust.sourceOfFunds" class="textarea" /></UiField>
        <label class="check"><input v-model="cust.uboVerified" type="checkbox"> Ultimate beneficial owners verified</label>
        <UiField label="Senior management approver" hint="Must be MLRO, CCO or CRO, and not you." required><UiUserSelect v-model="cust.seniorApproverId" :roles="['MLRO', 'CCO', 'CRO']" :exclude-id="user?.id" /></UiField>
      </template>
      <UiField v-else-if="custAction?.action === 'rescreen'" label="Screening result (Refinitiv/LSEG)" required>
        <select v-model="cust.result" class="select"><option value="clear">Clear</option><option value="potential_match">Potential match</option><option value="confirmed_match">Confirmed match</option></select>
      </UiField>
      <UiField v-else label="Verification note" required><textarea v-model="cust.note" class="textarea" placeholder="Documents checked, liveness result, database matches" /></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'aml:read' })
useHead({ title: 'Customers and screening' })
const { can: allowed, user } = useAccess()

// Cases moved to their own screen; keep old links (/aml?case=12) working.
const route = useRoute()
if (route.query.case) await navigateTo({ path: '/cases', query: { case: route.query.case } }, { replace: true })

const { data: overview, refresh: refreshOverview } = await useFetch('/api/aml/overview')
const { data: heldData, refresh: refreshHeld } = await useFetch('/api/aml/transactions', { query: { pending: 1 }, default: () => [] })
const { data: customerData, refresh: refreshCustomers } = await useFetch('/api/aml/customers', { default: () => [] })
const refreshAll = () => Promise.all([refreshOverview(), refreshHeld(), refreshCustomers()])
useLiveRefresh(['data', 'ccm'], () => Promise.all([refreshOverview(), refreshHeld()]), 2000)

const held = computed(() => heldData.value.filter(t => t.amount >= 10_000))
const customers = computed(() => customerData.value)
const tab = ref(route.query.tab === 'held' ? 'held' : 'customers')

const txColumns = [
  { key: 'ref', label: 'Transaction', width: '170px' },
  { key: 'amount', label: 'Amount', width: '130px', align: 'right' as const },
  { key: 'customer', label: 'Customer', sortValue: (r: any) => r.customer.name },
  { key: 'counterparty', label: 'Counterparty' },
  { key: 'actions', label: '', width: '100px', sortable: false },
]
const customerColumns = [
  { key: 'name', label: 'Customer' },
  { key: 'riskRating', label: 'Rating', width: '120px' },
  { key: 'kyc', label: 'KYC', width: '170px', sortValue: (r: any) => r.kycStatus },
  { key: 'sanctionsStatus', label: 'Sanctions / PEP', width: '160px' },
  { key: 'eddStatus', label: 'EDD', width: '130px' },
  { key: 'actions', label: '', width: '330px', sortable: false },
]

const act = useAction()
type Held = (typeof held.value)[number]
const screening = ref<Held | null>(null)
const screen_ = reactive({ result: 'clear', note: '' })
watch(screening, () => Object.assign(screen_, { result: 'clear', note: '' }))
async function screen() {
  const res = await act.run(() => api.post<{ caseRef: string | null }>(`/api/aml/transactions/${screening.value!.id}/screen`, screen_))
  if (res) {
    useToast().success(res.caseRef ? `Hit recorded. Case ${res.caseRef} opened.` : 'Payment cleared')
    screening.value = null
    refreshAll()
  }
}

type Customer = (typeof customers.value)[number]
const custAction = ref<{ row: Customer, action: string } | null>(null)
const cust = reactive({ note: '', sourceOfFunds: '', uboVerified: false, seniorApproverId: null as number | null, result: 'clear' })
watch(custAction, () => Object.assign(cust, { note: '', sourceOfFunds: '', uboVerified: false, seniorApproverId: null, result: 'clear' }))
async function submitCustomer() {
  const { row, action } = custAction.value!
  const body = action === 'complete-edd' ? { sourceOfFunds: cust.sourceOfFunds, uboVerified: cust.uboVerified, seniorApproverId: cust.seniorApproverId }
    : action === 'rescreen' ? { result: cust.result } : { note: cust.note }
  if (await act.run(() => api.post(`/api/aml/customers/${row.id}/${action}`, body), 'Customer updated')) { custAction.value = null; refreshAll() }
}
async function quickCustomer(id: number, action: string) {
  if (await act.run(() => api.post(`/api/aml/customers/${id}/${action}`), 'EDD started')) refreshAll()
}
</script>
