<template>
  <UiModal :open="open" :title="obligation ? `Edit ${obligation.ref}` : 'Add obligation'" :pending="act.pending.value" @close="emit('close')" @submit="save">
    <UiField label="Obligation" required><input v-model="form.title" class="input"></UiField>
    <div class="form-grid">
      <UiField label="Source"><select v-model="form.sourceType" class="select"><option v-for="s in ['law', 'regulation', 'standard', 'internal_policy']" :key="s" :value="s">{{ labelize(s) }}</option></select></UiField>
      <UiField label="Framework" required><input v-model="form.framework" class="input" placeholder="e.g. FCA SYSC"></UiField>
      <UiField label="Regulator"><input v-model="form.regulator" class="input"></UiField>
      <UiField label="Jurisdiction" required><input v-model="form.jurisdiction" class="input"></UiField>
      <UiField label="Owner" required><UiUserSelect v-model="form.ownerId" /></UiField>
      <UiField label="Status"><select v-model="form.status" class="select"><option v-for="s in ['compliant', 'partial', 'non_compliant', 'not_assessed']" :key="s" :value="s">{{ labelize(s) }}</option></select></UiField>
      <UiField label="Next review"><input v-model="form.nextReviewAt" type="date" class="input"></UiField>
    </div>
    <UiField label="Mapped controls">
      <select v-model="form.controlIds" class="select" multiple size="6"><option v-for="c in controls" :key="c.id" :value="c.id">{{ c.ref }}: {{ c.name }}</option></select>
    </UiField>
  </UiModal>
</template>

<script setup lang="ts">
interface ObligationLike { id: number, ref: string, title: string, sourceType: string, framework: string, regulator: string | null, jurisdiction: string, ownerId: number, status: string, nextReviewAt: string | Date | null, controlIds: number[] }
const props = defineProps<{ open: boolean, obligation: ObligationLike | null }>()
const emit = defineEmits<{ close: [], saved: [] }>()
const { data: controls } = useFetch('/api/controls', { default: () => [], key: 'controls-lookup', server: false })
const blank = () => ({ title: '', sourceType: 'regulation', framework: '', regulator: '', jurisdiction: 'UK', ownerId: null as number | null, status: 'not_assessed', nextReviewAt: '', controlIds: [] as number[] })
const form = reactive(blank())
watch(() => [props.open, props.obligation], () => {
  if (!props.open) return
  Object.assign(form, blank())
  const o = props.obligation
  if (o) Object.assign(form, { ...o, regulator: o.regulator ?? '', nextReviewAt: o.nextReviewAt ? new Date(o.nextReviewAt).toISOString().slice(0, 10) : '' })
}, { immediate: true })
const act = useAction()
async function save() {
  const body = { title: form.title, sourceType: form.sourceType, framework: form.framework, regulator: form.regulator || null, jurisdiction: form.jurisdiction, ownerId: form.ownerId, status: form.status, nextReviewAt: form.nextReviewAt || null, controlIds: form.controlIds }
  const ok = props.obligation
    ? await act.run(() => api.patch(`/api/obligations/${props.obligation!.id}`, body), 'Obligation updated')
    : await act.run(() => api.post('/api/obligations', body), 'Obligation added')
  if (ok) emit('saved')
}
</script>
