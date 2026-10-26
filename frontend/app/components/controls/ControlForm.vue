<template>
  <UiModal :open="open" :title="control ? `Edit ${control.ref}` : 'New control'" :pending="act.pending.value" @close="emit('close')" @submit="save">
    <UiField label="Name" required><input v-model="form.name" class="input"></UiField>
    <UiField label="Description"><textarea v-model="form.description" class="textarea" /></UiField>
    <div class="form-grid">
      <UiField label="Owner" required><UiUserSelect v-model="form.ownerId" /></UiField>
      <UiField label="Business unit" required><select v-model="form.businessUnit" class="select"><option v-for="b in BUSINESS_UNITS" :key="b">{{ b }}</option></select></UiField>
      <UiField label="Type"><select v-model="form.type" class="select"><option v-for="t in ['preventive', 'detective', 'corrective']" :key="t" :value="t">{{ labelize(t) }}</option></select></UiField>
      <UiField label="Frequency"><select v-model="form.frequency" class="select"><option v-for="f in ['continuous', 'daily', 'weekly', 'monthly', 'quarterly', 'annual']" :key="f" :value="f">{{ labelize(f) }}</option></select></UiField>
      <UiField label="CCM rule" hint="Links this control to continuous monitoring.">
        <select v-model="form.ccmRuleId" class="select"><option :value="null">None</option><option v-for="r in rules?.rules ?? []" :key="r.id" :value="r.id">{{ r.id }}: {{ r.name }}</option></select>
      </UiField>
    </div>
    <div class="row-wrap">
      <label class="check"><input v-model="form.automated" type="checkbox"> Automated</label>
      <label class="check"><input v-model="form.keyControl" type="checkbox"> Key control</label>
    </div>
  </UiModal>
</template>

<script setup lang="ts">
interface ControlLike { id: number, ref: string, name: string, description: string | null, ownerId: number, businessUnit: string, type: string, frequency: string, automated: boolean, keyControl: boolean, ccmRuleId: string | null }
const props = defineProps<{ open: boolean, control: ControlLike | null }>()
const emit = defineEmits<{ close: [], saved: [] }>()
const { data: rules } = useFetch('/api/ccm/rules', { key: 'ccm-lookup', server: false })
const blank = () => ({ name: '', description: '', ownerId: null as number | null, businessUnit: BUSINESS_UNITS[0] as string, type: 'preventive', frequency: 'continuous', automated: true, keyControl: true, ccmRuleId: null as string | null })
const form = reactive(blank())
watch(() => [props.open, props.control], () => {
  if (!props.open) return
  Object.assign(form, blank(), props.control ? { ...props.control, description: props.control.description ?? '' } : {})
}, { immediate: true })
const act = useAction()
async function save() {
  const body = { name: form.name, description: form.description || null, ownerId: form.ownerId, businessUnit: form.businessUnit, type: form.type, frequency: form.frequency, automated: form.automated, keyControl: form.keyControl, ccmRuleId: form.ccmRuleId }
  const ok = props.control
    ? await act.run(() => api.patch(`/api/controls/${props.control!.id}`, body), 'Control updated')
    : await act.run(() => api.post('/api/controls', body), 'Control added to library')
  if (ok) emit('saved')
}
</script>
