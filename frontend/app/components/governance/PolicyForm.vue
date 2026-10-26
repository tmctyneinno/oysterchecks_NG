<template>
  <UiModal :open="open" :title="policy ? `Edit ${policy.ref}` : 'New policy'" :pending="act.pending.value" @close="emit('close')" @submit="save">
    <UiField label="Title" required><input v-model="form.title" class="input"></UiField>
    <UiField label="Summary"><textarea v-model="form.summary" class="textarea" rows="4" /></UiField>
    <div class="form-grid">
      <UiField label="Owner" required><UiUserSelect v-model="form.ownerId" /></UiField>
      <UiField label="Jurisdiction" required><input v-model="form.jurisdiction" class="input"></UiField>
      <UiField label="Frameworks" hint="Comma separated"><input v-model="frameworks" class="input" placeholder="UK GDPR, ISO 27001"></UiField>
      <UiField label="Review date" required><input v-model="form.reviewDate" type="date" class="input"></UiField>
    </div>
    <div class="form-grid">
      <UiField label="Linked risks"><select v-model="form.riskIds" class="select" multiple size="5"><option v-for="r in risks" :key="r.id" :value="r.id">{{ r.ref }} {{ r.title }}</option></select></UiField>
      <UiField label="Linked controls"><select v-model="form.controlIds" class="select" multiple size="5"><option v-for="c in controls" :key="c.id" :value="c.id">{{ c.ref }} {{ c.name }}</option></select></UiField>
    </div>
  </UiModal>
</template>

<script setup lang="ts">
interface PolicyLike { id: number, ref: string, title: string, summary: string | null, ownerId: number, jurisdiction: string, frameworks: string[], reviewDate: string | Date, riskIds: number[], controlIds: number[], obligationIds: number[] }
const props = defineProps<{ open: boolean, policy: PolicyLike | null }>()
const emit = defineEmits<{ close: [], saved: [] }>()
const { can: allowed } = useAccess()
const { data: controls } = useFetch('/api/controls', { default: () => [], key: 'controls-lookup', server: false })
const { data: risks } = useFetch('/api/risks', { default: () => [], key: 'risks-lookup', server: false, immediate: allowed('risk:read') })

const blank = () => ({ title: '', summary: '', ownerId: null as number | null, jurisdiction: 'UK', reviewDate: '', riskIds: [] as number[], controlIds: [] as number[] })
const form = reactive(blank())
const frameworks = ref('')
watch(() => [props.open, props.policy], () => {
  if (!props.open) return
  Object.assign(form, blank())
  frameworks.value = ''
  const p = props.policy
  if (p) {
    Object.assign(form, { title: p.title, summary: p.summary ?? '', ownerId: p.ownerId, jurisdiction: p.jurisdiction, reviewDate: new Date(p.reviewDate).toISOString().slice(0, 10), riskIds: p.riskIds, controlIds: p.controlIds })
    frameworks.value = p.frameworks.join(', ')
  }
}, { immediate: true })

const act = useAction()
async function save() {
  const body = { ...form, summary: form.summary || null, frameworks: frameworks.value.split(',').map(s => s.trim()).filter(Boolean) }
  const ok = props.policy
    ? await act.run(() => api.patch(`/api/policies/${props.policy!.id}`, body), 'Policy saved')
    : await act.run(() => api.post('/api/policies', body), 'Draft policy created')
  if (ok) emit('saved')
}
</script>
