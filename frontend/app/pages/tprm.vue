<template>
  <div class="page">
    <UiPageHeader title="Third parties" :subtitle="`${suppliers.length} suppliers, ${reviewOverdue} reviews overdue`">
      <a href="/api/export/suppliers" class="btn"><i class="bi bi-download" aria-hidden="true" /> Export</a>
      <button v-if="allowed('tprm:write')" class="btn btn-primary" type="button" @click="dialog = 'onboard'"><i class="bi bi-plus-lg" aria-hidden="true" /> Onboard supplier</button>
    </UiPageHeader>

    <div class="kpi-grid">
      <UiKpiCard v-for="t in tiers" :key="t" :label="`${labelize(t)} tier`" :value="suppliers.filter(s => s.tier === t && s.status !== 'exited').length" :tone="t === 'critical' ? 'critical' : t === 'high' ? 'high' : t === 'medium' ? 'medium' : 'neutral'" :sub="`Review every ${SUPPLIER_REVIEW_MONTHS[t]} months`" />
    </div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="suppliers" clickable :active-key="selectedId" @row-click="r => selectedId = r.id">
        <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.services }} · {{ row.country }}</div></template>
        <template #cell-tier="{ row }"><UiPill :value="row.tier === 'low' ? 'neutral' : row.tier" :text="labelize(row.tier)" /></template>
        <template #cell-dd="{ row }"><UiPill :value="row.ddStatus" /><div class="cell-sub">{{ row.questionnaireScore !== null ? `Questionnaire ${row.questionnaireScore}/100` : 'No questionnaire' }}</div></template>
        <template #cell-sanctionsStatus="{ row }"><UiPill :value="row.sanctionsStatus" /></template>
        <template #cell-monitoring="{ row }">
          <span v-if="row.monitoringFlags.length" class="pill tone-high">{{ plural(row.monitoringFlags.length, 'alert') }}</span>
          <span v-else class="small muted">No alerts</span>
        </template>
        <template #cell-nextReviewAt="{ row }"><span class="small" :style="row.reviewOverdue ? 'color:var(--critical)' : ''">{{ fmtDate(row.nextReviewAt) }}</span><div v-if="row.renewalSoon" class="cell-sub">Renewal {{ fmtDate(row.contractRenewalAt) }}</div></template>
      </UiDataTable>
    </UiPanel>

    <UiDrawer :open="!!selected" :title="selected?.name ?? ''" :eyebrow="selected ? `${selected.ref} · ${selected.services}` : ''" @close="selectedId = null">
      <template v-if="selected" #meta><UiPill :value="selected.tier === 'low' ? 'neutral' : selected.tier" :text="`${labelize(selected.tier)} tier`" /><UiPill :value="selected.status === 'active' ? 'ok' : 'neutral'" :text="labelize(selected.status)" /></template>
      <template v-if="selected">
        <div v-if="selected.monitoringFlags.length" class="callout warn"><i class="bi bi-broadcast" aria-hidden="true" /><div class="small"><strong>Ongoing monitoring alerts</strong><ul style="margin:4px 0 0;padding-left:18px"><li v-for="fl in selected.monitoringFlags" :key="fl">{{ fl }}</li></ul></div></div>
        <dl class="kv">
          <dt>Relationship owner</dt><dd>{{ selected.ownerName }}</dd>
          <dt>Data access</dt><dd>{{ selected.dataAccess ? 'Yes, processes our data' : 'No' }}<span v-if="selected.dataAccess"> · DPA {{ selected.dpaInPlace ? 'in place' : 'missing' }}</span></dd>
          <dt>Annual spend</dt><dd class="num">{{ fmtGBP(selected.annualSpend) }}</dd>
          <dt>Due diligence</dt><dd>{{ labelize(selected.ddStatus) }}<span v-if="selected.questionnaireScore !== null"> · questionnaire {{ selected.questionnaireScore }}/100</span></dd>
          <dt>Sanctions / adverse media</dt><dd><UiPill :value="selected.sanctionsStatus" /></dd>
          <dt>Last review</dt><dd>{{ fmtDate(selected.lastReviewAt) }}</dd>
          <dt>Next review</dt><dd>{{ fmtDate(selected.nextReviewAt) }}</dd>
          <dt>Contract renewal</dt><dd>{{ fmtDate(selected.contractRenewalAt) }}</dd>
        </dl>
        <UiEvidence entity-type="supplier" :entity-id="selected.id" :can-upload="allowed('tprm:write')" />
      </template>
      <template v-if="selected && allowed('tprm:write') && selected.status !== 'exited'" #footer>
        <button v-if="selected.ddStatus !== 'complete'" class="btn btn-primary" type="button" @click="dialog = 'complete-dd'">Complete due diligence</button>
        <button v-else class="btn btn-primary" type="button" @click="dialog = 'review'">Record periodic review</button>
        <button class="btn" type="button" @click="dialog = 'flag'">Add monitoring alert</button>
        <button v-if="selected.monitoringFlags.length" class="btn" type="button" @click="dialog = 'clear-flags'">Resolve alerts</button>
        <span class="spacer" />
        <button class="btn btn-ghost" type="button" @click="dialog = 'exit'">Exit supplier</button>
      </template>
    </UiDrawer>

    <UiModal :open="dialog === 'onboard'" title="Onboard supplier" submit-label="Register & screen" :pending="act.pending.value" @close="dialog = null" @submit="onboard">
      <UiField label="Name" required><input v-model="sup.name" class="input"></UiField>
      <UiField label="Services provided" required><input v-model="sup.services" class="input"></UiField>
      <div class="form-grid">
        <UiField label="Country (ISO code)" required><input v-model="sup.country" class="input mono" maxlength="2" @input="sup.country = sup.country.toUpperCase()"></UiField>
        <UiField label="Annual spend (£)" required><input v-model.number="sup.annualSpend" type="number" min="0" class="input"></UiField>
        <UiField label="Relationship owner" required><UiUserSelect v-model="sup.ownerId" /></UiField>
        <UiField label="Contract renewal"><input v-model="sup.contractRenewalAt" type="date" class="input"></UiField>
      </div>
      <div class="row-wrap">
        <label class="check"><input v-model="sup.dataAccess" type="checkbox"> Accesses our data</label>
        <label class="check"><input v-model="sup.dpaInPlace" type="checkbox"> DPA in place</label>
        <label class="check"><input v-model="sup.regulatoryCritical" type="checkbox"> Supports a regulated / important business service</label>
      </div>
      <p class="small muted">Risk tier is calculated from data access, spend, regulatory criticality and geography. Sanctions and adverse-media screening is requested automatically.</p>
    </UiModal>

    <UiModal :open="dialog === 'complete-dd'" title="Complete due diligence" :pending="act.pending.value" @close="dialog = null" @submit="send('complete-dd', { questionnaireScore: dd.score, sanctionsStatus: dd.sanctions, regulatoryCritical: dd.regulatoryCritical })">
      <div class="form-grid">
        <UiField label="Questionnaire score (0–100)" required><input v-model.number="dd.score" type="number" min="0" max="100" class="input"></UiField>
        <UiField label="Screening result" required><select v-model="dd.sanctions" class="select"><option value="clear">Clear</option><option value="potential_match">Potential match</option><option value="confirmed_match">Confirmed match</option></select></UiField>
      </div>
      <label class="check"><input v-model="dd.regulatoryCritical" type="checkbox"> Supports a regulated / important business service</label>
    </UiModal>
    <UiModal :open="dialog === 'review'" title="Periodic review" :pending="act.pending.value" @close="dialog = null" @submit="send('review', { note: text, ...(dd.score ? { questionnaireScore: dd.score } : {}) })">
      <UiField label="Review notes" required><textarea v-model="text" class="textarea" /></UiField>
      <UiField label="Refreshed questionnaire score"><input v-model.number="dd.score" type="number" min="0" max="100" class="input"></UiField>
    </UiModal>
    <UiModal :open="dialog === 'flag'" title="Add monitoring alert" :pending="act.pending.value" @close="dialog = null" @submit="send('flag', { flag: text })">
      <UiField label="Alert" hint="Financial instability, sanctions match, ESG, cyber incident or adverse media" required><input v-model="text" class="input"></UiField>
    </UiModal>
    <UiModal :open="dialog === 'clear-flags' || dialog === 'exit'" :title="dialog === 'exit' ? 'Exit supplier' : 'Resolve monitoring alerts'" :danger="dialog === 'exit'" :pending="act.pending.value" @close="dialog = null" @submit="send(dialog!, { note: text })">
      <UiField label="Note" required><textarea v-model="text" class="textarea" /></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'tprm:read' })
useHead({ title: 'Third parties' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/suppliers', { default: () => [] })
const suppliers = computed(() => data.value)
const selectedId = useDrawerQuery('id')
const selected = computed(() => suppliers.value.find(s => s.id === selectedId.value) ?? null)
const tiers = ['critical', 'high', 'medium', 'low'] as const
const reviewOverdue = computed(() => suppliers.value.filter(s => s.reviewOverdue).length)
const flagged = computed(() => suppliers.value.filter(s => s.monitoringFlags.length).length)
const tierRank = { critical: 0, high: 1, medium: 2, low: 3 } as Record<string, number>
const columns = [
  { key: 'name', label: 'Supplier' },
  { key: 'tier', label: 'Tier', width: '110px', sortValue: (r: any) => tierRank[r.tier]! },
  { key: 'dd', label: 'Due diligence', width: '170px', sortable: false },
  { key: 'sanctionsStatus', label: 'Screening', width: '150px' },
  { key: 'monitoring', label: 'Monitoring', width: '140px', sortValue: (r: any) => r.monitoringFlags.length },
  { key: 'nextReviewAt', label: 'Next review', width: '140px', sortValue: (r: any) => r.nextReviewAt ? new Date(r.nextReviewAt).getTime() : Infinity },
]

const dialog = ref<string | null>(null)
const text = ref('')
const dd = reactive({ score: 75, sanctions: 'clear', regulatoryCritical: false })
watch(dialog, () => { text.value = ''; Object.assign(dd, { score: selected.value?.questionnaireScore ?? 75, sanctions: 'clear', regulatoryCritical: false }) })
const sup = reactive({ name: '', services: '', country: 'GB', annualSpend: 0, ownerId: null as number | null, contractRenewalAt: '', dataAccess: false, dpaInPlace: false, regulatoryCritical: false })
const act = useAction()
async function send(action: string, body: object) {
  if (await act.run(() => api.post(`/api/suppliers/${selected.value!.id}/${action}`, body), 'Supplier updated')) { dialog.value = null; refresh() }
}
async function onboard() {
  const row = await act.run(() => api.post<{ id: number, tier: string }>('/api/suppliers', { ...sup, contractRenewalAt: sup.contractRenewalAt || null }))
  if (row) {
    useToast().success(`Supplier registered as ${row.tier} tier`)
    dialog.value = null
    await refresh()
    selectedId.value = row.id
  }
}
</script>
