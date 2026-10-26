<template>
  <div class="page">
    <UiPageHeader title="Access reviews" subtitle="Privileged accounts must be recertified every 90 days" />

    <div class="kpi-grid">
      <UiKpiCard label="Privileged accounts" :value="accounts.filter(a => a.privileged).length" tone="medium" />
      <UiKpiCard label="Review overdue (> 90 days)" :value="accounts.filter(a => a.overdue).length" :tone="accounts.some(a => a.overdue) ? 'critical' : 'ok'" />
      <UiKpiCard label="Reviewed in last 30 days" :value="accounts.filter(a => a.reviewAgeDays !== null && a.reviewAgeDays <= 30).length" tone="ok" />
    </div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="accounts">
        <template #cell-account="{ row }"><div class="cell-title mono">{{ row.account }}</div><div class="cell-sub">{{ row.holder }}</div></template>
        <template #cell-groups="{ row }"><div class="chip-list"><span v-for="g in row.groups" :key="g" class="chip">{{ g }}</span></div></template>
        <template #cell-privileged="{ row }"><UiPill :value="row.privileged ? 'high' : 'neutral'" :text="row.privileged ? 'Privileged' : 'Standard'" /></template>
        <template #cell-review="{ row }"><span class="small" :style="row.overdue ? 'color:var(--critical)' : ''">{{ row.reviewAgeDays === null ? 'Never' : `${row.reviewAgeDays} days ago` }}</span><div v-if="row.reviewedByName" class="cell-sub">{{ row.reviewedByName }}</div></template>
        <template #cell-actions="{ row }"><button v-if="allowed('security:write') && row.privileged" class="btn btn-sm btn-primary" type="button" @click="reviewing = row">Recertify</button></template>
      </UiDataTable>
    </UiPanel>

    <UiModal :open="!!reviewing" :title="reviewing ? `Recertify ${reviewing.account}` : ''" submit-label="Record decision" :danger="form.decision === 'revoke'" :pending="act.pending.value" @close="reviewing = null" @submit="submit">
      <UiField label="Decision" required>
        <div class="row"><label class="check"><input v-model="form.decision" type="radio" value="retain"> Retain access</label><label class="check"><input v-model="form.decision" type="radio" value="revoke"> Revoke privileged access</label></div>
      </UiField>
      <UiField label="Justification" required><textarea v-model="form.note" class="textarea" /></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'security:read' })
useHead({ title: 'Access reviews' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/security/access', { default: () => [] })
const accounts = computed(() => data.value)
const columns = [
  { key: 'account', label: 'Account' },
  { key: 'system', label: 'System', width: '160px' },
  { key: 'groups', label: 'Groups', sortable: false },
  { key: 'privileged', label: 'Type', width: '130px' },
  { key: 'review', label: 'Last review', width: '160px', sortValue: (r: any) => r.reviewAgeDays ?? 9999 },
  { key: 'actions', label: '', width: '120px', sortable: false },
]
type Account = (typeof accounts.value)[number]
const reviewing = ref<Account | null>(null)
const form = reactive({ decision: 'retain', note: '' })
watch(reviewing, () => Object.assign(form, { decision: 'retain', note: '' }))
const act = useAction()
async function submit() {
  if (await act.run(() => api.post(`/api/security/access/${reviewing.value!.id}/review`, form), 'Recertification recorded')) { reviewing.value = null; refresh() }
}
</script>
