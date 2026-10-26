<template>
  <UiDrawer :open="!!id" :title="c ? `${c.customer.name}` : 'Case'" :eyebrow="c ? `${c.ref}, ${labelize(c.type)} case` : ''" :loading="pending && !c" @close="emit('close')">
    <template v-if="c" #meta><UiPill :value="c.status" /><AmlScore :score="c.score" /></template>
    <template v-if="c">
      <section>
        <div class="section-title">Score breakdown</div>
        <p class="small muted" style="margin-bottom:8px">Each factor's contribution to the score is shown. A person must review the case before any adverse decision.</p>
        <ChartsBarList :rows="c.scoreFactors.map(f => ({ label: f.factor, value: f.weight }))" color="var(--purple)" label="Score contributions" />
      </section>
      <dl class="kv">
        <dt>Customer</dt><dd>{{ c.customer.name }} <span class="mono muted">{{ c.customer.ref }}</span></dd>
        <dt>Profile</dt><dd>{{ labelize(c.customer.type) }} · {{ c.customer.country }} · {{ labelize(c.customer.riskRating) }} risk{{ c.customer.pep ? ' · PEP' : '' }}</dd>
        <dt>Sanctions</dt><dd><UiPill :value="c.customer.sanctionsStatus" /></dd>
        <dt>Analyst</dt><dd>{{ c.analystName ?? 'Unassigned' }}</dd>
        <dt>Opened</dt><dd class="mono">{{ fmtDateTime(c.openedAt) }}</dd>
        <template v-if="c.sarDecisionAt"><dt>SAR decision</dt><dd class="mono">{{ fmtDateTime(c.sarDecisionAt) }} <span class="muted">(NCA deadline {{ fmtDate(new Date(new Date(c.sarDecisionAt).getTime() + 7 * 864e5)) }})</span></dd></template>
        <template v-if="c.sarSubmittedAt"><dt>Submitted to NCA</dt><dd>{{ fmtDateTime(c.sarSubmittedAt) }} · <span class="mono">{{ c.ncaReference }}</span></dd></template>
        <template v-if="c.outcome"><dt>Outcome</dt><dd>{{ c.outcome }}</dd></template>
      </dl>
      <section v-if="c.sarDraft">
        <div class="section-title">SAR draft</div>
        <pre class="sar">{{ c.sarDraft }}</pre>
      </section>
      <section>
        <div class="section-title">Recent transactions</div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Date</th><th>Counterparty</th><th style="text-align:right">Amount</th><th>Screening</th></tr></thead>
            <tbody>
              <tr v-for="t in c.transactions.slice(0, 12)" :key="t.id" :class="{ selected: t.id === c.flaggedTransactionId }">
                <td class="mono small">{{ fmtDateTime(t.at) }}</td>
                <td class="small"><span class="muted">{{ t.direction === 'out' ? 'To' : 'From' }}</span> {{ t.counterparty }} <span class="muted">{{ t.counterpartyCountry }}</span></td>
                <td class="num small" style="text-align:right">{{ fmtGBP(t.amount) }}</td>
                <td><UiPill :value="t.screeningStatus" /></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      <UiEvidence entity-type="aml_case" :entity-id="c.id" :can-upload="allowed('aml:write')" />
    </template>
    <template v-if="c && !['closed', 'sar_submitted'].includes(c.status)" #footer>
      <template v-if="allowed('aml:write') && c.status !== 'sar_decided'">
        <button class="btn" type="button" @click="dialog = 'assign'">Assign</button>
        <button v-if="c.status !== 'escalated'" class="btn" type="button" @click="dialog = 'escalate'">Escalate to MLRO</button>
        <button class="btn btn-ghost" type="button" @click="dialog = 'dismiss'">Dismiss</button>
      </template>
      <span class="spacer" />
      <button v-if="c.canSubmitSar && c.status !== 'sar_decided'" class="btn btn-primary" type="button" @click="dialog = 'sar-decision'">Decide to file SAR</button>
      <button v-if="c.canSubmitSar && c.status === 'sar_decided'" class="btn btn-primary" type="button" @click="dialog = 'sar-submit'">Confirm NCA submission</button>
    </template>
  </UiDrawer>

  <UiModal :open="dialog === 'assign'" title="Assign case" :pending="act.pending.value" :disabled="!form.analystId" @close="dialog = null" @submit="send('assign', { analystId: form.analystId })">
    <UiField label="Analyst" required><UiUserSelect v-model="form.analystId" :roles="['AML_ANALYST', 'MLRO']" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'escalate'" title="Escalate to MLRO" submit-label="Escalate" :pending="act.pending.value" @close="dialog = null" @submit="send('escalate', { note: form.text })">
    <UiField label="Analyst summary" required><textarea v-model="form.text" class="textarea" rows="4" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'dismiss'" title="Dismiss case" submit-label="Dismiss" danger :pending="act.pending.value" @close="dialog = null" @submit="send('dismiss', { outcome: form.text })">
    <UiField label="Rationale" required><textarea v-model="form.text" class="textarea" rows="4" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'sar-decision'" title="Record SAR decision" submit-label="Record decision" :pending="act.pending.value" @close="dialog = null" @submit="send('sar-decision', { grounds: form.text })">
    <div class="callout warn small"><i class="bi bi-clock" aria-hidden="true" /> The SAR must be submitted to the NCA within 7 days of this decision.</div>
    <UiField label="Grounds for suspicion" hint="Added to the draft. Do not disclose to the customer." required><textarea v-model="form.text" class="textarea" rows="6" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'sar-submit'" title="Confirm NCA submission" submit-label="Confirm submitted" :pending="act.pending.value" @close="dialog = null" @submit="send('sar-submit', { ncaReference: form.text })">
    <UiField label="NCA SAR Online reference" required><input v-model="form.text" class="input mono"></UiField>
  </UiModal>
</template>

<script setup lang="ts">
const props = defineProps<{ id: number | null }>()
const emit = defineEmits<{ close: [], changed: [] }>()
const { can: allowed } = useAccess()
const { data: c, pending, refresh } = useFetch(() => `/api/aml/cases/${props.id}`, { immediate: false, watch: false })
watch(() => props.id, id => { if (id) refresh() }, { immediate: true })

const dialog = ref<string | null>(null)
const form = reactive({ text: '', analystId: null as number | null })
watch(dialog, () => Object.assign(form, { text: '', analystId: c.value?.analystId ?? null }))
const act = useAction()
async function send(action: string, body: object) {
  if (await act.run(() => api.post(`/api/aml/cases/${props.id}/${action}`, body), 'Case updated')) {
    dialog.value = null
    await refresh()
    emit('changed')
  }
}
</script>

<style scoped>
.sar { white-space: pre-wrap; font-family: var(--font-mono); font-size: 12px; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; padding: 12px; margin: 0; }
</style>
