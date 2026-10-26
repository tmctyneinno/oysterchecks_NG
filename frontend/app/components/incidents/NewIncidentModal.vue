<template>
  <UiModal :open="open" title="Log incident" submit-label="Log incident" :pending="act.pending.value" @close="emit('close')" @submit="save">
    <UiField label="Title" required><input v-model="form.title" class="input"></UiField>
    <UiField label="What happened?"><textarea v-model="form.description" class="textarea" rows="4" /></UiField>
    <div class="form-grid">
      <UiField label="Type" required><select v-model="form.type" class="select"><option v-for="t in types" :key="t" :value="t">{{ labelize(t) }}</option></select></UiField>
      <UiField label="Initial severity" required><select v-model="form.severity" class="select"><option v-for="s in SEVERITIES" :key="s" :value="s">{{ labelize(s) }}</option></select></UiField>
      <UiField v-if="roleDef?.scope !== 'bu'" label="Business unit" required><select v-model="form.businessUnit" class="select"><option v-for="b in BUSINESS_UNITS" :key="b">{{ b }}</option></select></UiField>
      <UiField label="When did it occur?" required><input v-model="form.occurredAt" type="datetime-local" class="input"></UiField>
    </div>
  </UiModal>
</template>

<script setup lang="ts">
const props = defineProps<{ open: boolean, preset?: { title?: string, description?: string, severity?: string, businessUnit?: string | null, sourceAlertId?: number } }>()
const emit = defineEmits<{ close: [], created: [id: number] }>()
const { roleDef, user } = useAccess()
const types = ['operational', 'data', 'financial_crime', 'regulatory', 'audit_finding', 'cyber']
const nowLocal = () => { const d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 16) }
const blank = () => ({ title: '', description: '', type: 'operational', severity: 'medium', businessUnit: user.value?.businessUnit ?? BUSINESS_UNITS[0] as string, occurredAt: nowLocal(), sourceAlertId: undefined as number | undefined })
const form = reactive(blank())
watch(() => props.open, (o) => {
  if (o) Object.assign(form, blank(), Object.fromEntries(Object.entries(props.preset ?? {}).filter(([, v]) => v != null)))
})
const act = useAction()
async function save() {
  const row = await act.run(() => api.post<{ id: number, ref: string }>('/api/incidents', { ...form, occurredAt: new Date(form.occurredAt).toISOString() }))
  if (row) {
    useToast().success(`${row.ref} logged`)
    emit('created', row.id)
  }
}
</script>
