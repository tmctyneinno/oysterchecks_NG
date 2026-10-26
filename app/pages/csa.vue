<template>
  <div class="page">
    <UiPageHeader title="Self-assessments" :subtitle="`${user?.businessUnit ? user.businessUnit + ': ' : ''}${due.length} due, ${overdue.length} overdue`" />

    <div class="kpi-grid">
      <UiKpiCard label="Due this period" :value="due.length" tone="medium" />
      <UiKpiCard label="Overdue" :value="overdue.length" :tone="overdue.length ? 'critical' : 'ok'" sub="Escalated after 3 business days" />
      <UiKpiCard label="Submitted" :value="items.filter(i => i.submittedAt).length" tone="ok" />
      <UiKpiCard label="Disagree with tests" :value="items.filter(i => i.divergent).length" tone="high" sub="Self-assessment differs from test evidence" />
    </div>

    <div class="callout info small"><i class="bi bi-info-circle" aria-hidden="true" /> Self-assessments are recorded alongside, not instead of, independent test results. Where they disagree, the 2nd line is prompted to investigate.</div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="items">
        <template #cell-control="{ row }"><div class="cell-title">{{ row.control.name }}</div><div class="cell-sub"><span class="mono">{{ row.control.ref }}</span> · {{ row.businessUnit }} · {{ labelize(row.control.frequency) }}</div></template>
        <template #cell-dueAt="{ row }"><span class="small" :style="row.overdue ? 'color:var(--critical)' : ''">{{ fmtDate(row.dueAt) }}</span><div class="cell-sub">{{ row.period }}</div></template>
        <template #cell-result="{ row }">
          <div v-if="row.result" class="row" style="gap:6px"><UiPill :value="row.result" /><UiPill v-if="row.divergent" value="warn" :text="`Testing: ${labelize(row.control.effectiveness).toLowerCase()}`" /></div>
          <UiPill v-else-if="row.overdue" value="overdue" text="Overdue" />
          <span v-else class="muted small">Not submitted</span>
        </template>
        <template #cell-submitted="{ row }"><span v-if="row.submittedAt" class="small">{{ fmtDate(row.submittedAt) }} · {{ row.submittedByName }}</span></template>
        <template #cell-actions="{ row }">
          <button v-if="!row.submittedAt && allowed('csa:submit')" class="btn btn-sm btn-primary" type="button" @click="open(row)">Assess</button>
        </template>
      </UiDataTable>
    </UiPanel>

    <UiModal :open="!!current" :title="current ? `Assess ${current.control.ref}` : ''" submit-label="Submit assessment" :pending="act.pending.value" @close="current = null" @submit="submit">
      <template v-if="current">
        <p class="small text-2">{{ current.control.name }}, {{ current.period }}</p>
        <UiField label="Is this control operating effectively?" required>
          <div class="stack" style="gap:6px">
            <label v-for="o in options" :key="o" class="check"><input v-model="form.result" type="radio" :value="o"> {{ labelize(o) }}</label>
          </div>
        </UiField>
        <UiField label="Comment" :hint="form.result !== 'effective' ? 'Required. Describe the weakness and any interim fix.' : 'Optional'"><textarea v-model="form.comment" class="textarea" /></UiField>
        <UiEvidence entity-type="csa" :entity-id="current.id" can-upload />
      </template>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'csa:read' })
useHead({ title: 'Self-assessments' })
const { can: allowed, user } = useAccess()
const { data, refresh } = await useFetch('/api/csa', { default: () => [] })
const items = computed(() => data.value)
const due = computed(() => items.value.filter(i => !i.submittedAt))
const overdue = computed(() => items.value.filter(i => i.overdue))
const options = ['effective', 'partially_effective', 'ineffective'] as const

const columns = [
  { key: 'control', label: 'Control', sortValue: (r: any) => r.control.ref },
  { key: 'dueAt', label: 'Due', width: '130px', sortValue: (r: any) => new Date(r.dueAt).getTime() },
  { key: 'result', label: 'Result', width: '300px', sortable: false },
  { key: 'submitted', label: 'Submitted', width: '200px', sortable: false },
  { key: 'actions', label: '', width: '100px', sortable: false },
]

type Item = (typeof items.value)[number]
const current = ref<Item | null>(null)
const form = reactive({ result: 'effective', comment: '' })
const open = (row: Item) => { current.value = row; Object.assign(form, { result: 'effective', comment: '' }) }
const act = useAction()
async function submit() {
  if (await act.run(() => api.post(`/api/csa/${current.value!.id}/submit`, { result: form.result, comment: form.comment || undefined }), 'Assessment submitted')) { current.value = null; refresh() }
}
</script>
