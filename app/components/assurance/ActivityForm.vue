<template>
  <UiModal :open="open" title="Plan assurance activity" submit-label="Add to plan" :pending="act.pending.value" @close="emit('close')" @submit="save">
    <UiField label="Title" required><input v-model="form.title" class="input"></UiField>
    <div class="form-grid">
      <UiField label="Line" required><select v-model.number="form.line" class="select"><option :value="1">1st line</option><option :value="2">2nd line</option><option :value="3">3rd line</option></select></UiField>
      <UiField label="Provider" required><select v-model="form.provider" class="select"><option v-for="p in providers[form.line]" :key="p" :value="p">{{ labelize(p) }}</option></select></UiField>
      <UiField label="Type"><select v-model="form.activityType" class="select"><option v-for="t in ['csa', 'monitoring', 'testing', 'audit', 'review']" :key="t" :value="t">{{ t === 'csa' ? 'Self-assessment' : labelize(t) }}</option></select></UiField>
      <UiField label="Owner" required><UiUserSelect v-model="form.ownerId" /></UiField>
      <UiField label="Start" required><input v-model="form.plannedStart" type="date" class="input"></UiField>
      <UiField label="End" required><input v-model="form.plannedEnd" type="date" class="input"></UiField>
    </div>
    <UiField label="Controls in scope"><select v-model="form.controlIds" class="select" multiple size="6"><option v-for="c in controls" :key="c.id" :value="c.id">{{ c.ref }} {{ c.name }}</option></select></UiField>
  </UiModal>
</template>

<script setup lang="ts">
defineProps<{ open: boolean }>()
const emit = defineEmits<{ close: [], saved: [] }>()
const providers: Record<number, string[]> = { 1: ['management'], 2: ['compliance', 'risk'], 3: ['internal_audit', 'external_audit', 'regulator'] }
const { data: controls } = useFetch('/api/controls', { default: () => [], key: 'controls-lookup', server: false })
const form = reactive({ title: '', line: 2, provider: 'compliance', activityType: 'testing', ownerId: null as number | null, plannedStart: '', plannedEnd: '', controlIds: [] as number[] })
watch(() => form.line, l => { form.provider = providers[l]![0]! })
const act = useAction()
async function save() {
  if (await act.run(() => api.post('/api/assurance/activities', form), 'Activity planned')) emit('saved')
}
</script>
