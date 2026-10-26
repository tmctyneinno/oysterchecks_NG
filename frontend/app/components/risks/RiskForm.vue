<template>
  <UiModal :open="open" :title="risk ? `Edit ${risk.ref}` : 'Add risk'" :submit-label="risk ? 'Save' : 'Add to register'" :pending="act.pending.value" @close="emit('close')" @submit="save">
    <UiField label="Title" required><input v-model="form.title" class="input"></UiField>
    <UiField label="Description"><textarea v-model="form.description" class="textarea" /></UiField>
    <div class="form-grid">
      <UiField label="Category" required>
        <input v-model="form.category" class="input" list="risk-categories">
        <datalist id="risk-categories"><option v-for="c in categories" :key="c" :value="c" /></datalist>
      </UiField>
      <UiField label="Owner" required><UiUserSelect v-model="form.ownerId" /></UiField>
      <UiField label="Impact (1–5)" required><input v-model.number="form.impact" type="range" min="1" max="5" class="input"><span class="num small">{{ form.impact }}</span></UiField>
      <UiField label="Likelihood (1–5)" required><input v-model.number="form.likelihood" type="range" min="1" max="5" class="input"><span class="num small">{{ form.likelihood }}</span></UiField>
    </div>
    <p class="small text-2">Inherent score: <strong class="num">{{ form.impact * form.likelihood }}</strong> ({{ bandFor(form.impact * form.likelihood) }})</p>
    <div class="form-grid">
      <UiField label="Treatment" required>
        <select v-model="form.treatment" class="select"><option v-for="t in ['accept', 'mitigate', 'transfer', 'avoid']" :key="t" :value="t">{{ labelize(t) }}</option></select>
      </UiField>
      <UiField label="Treatment due"><input v-model="form.treatmentDueAt" type="date" class="input"></UiField>
      <UiField label="Business unit">
        <select v-model="form.businessUnit" class="select"><option :value="null">None</option><option v-for="b in BUSINESS_UNITS" :key="b">{{ b }}</option></select>
      </UiField>
    </div>
    <UiField label="Treatment plan"><textarea v-model="form.treatmentPlan" class="textarea" /></UiField>
    <UiField label="Mapped controls" hint="Residual risk is calculated from how these controls performed in testing.">
      <select v-model="form.controlIds" class="select" multiple size="6">
        <option v-for="c in controls" :key="c.id" :value="c.id">{{ c.ref }}: {{ c.name }} ({{ labelize(c.effectiveness) }})</option>
      </select>
    </UiField>
  </UiModal>
</template>

<script setup lang="ts">
interface RiskLike { id: number, ref: string, title: string, description: string | null, category: string, ownerId: number, businessUnit: string | null, impact: number, likelihood: number, treatment: string, treatmentPlan: string | null, treatmentDueAt: string | Date | null, controls: { id: number }[] }
const props = defineProps<{ open: boolean, risk: RiskLike | null }>()
const emit = defineEmits<{ close: [], saved: [] }>()
const categories = ['Financial Crime', 'Cyber & Data', 'Regulatory', 'Operational', 'Conduct', 'People', 'Model', 'Strategic']
const { data: controls } = useFetch('/api/controls', { default: () => [], key: 'controls-lookup', server: false })

const blank = () => ({ title: '', description: '', category: 'Operational', ownerId: null as number | null, businessUnit: null as string | null, impact: 3, likelihood: 3, treatment: 'mitigate', treatmentPlan: '', treatmentDueAt: '', controlIds: [] as number[] })
const form = reactive(blank())
watch(() => [props.open, props.risk], () => {
  if (!props.open) return
  Object.assign(form, blank())
  if (props.risk) {
    const r = props.risk
    Object.assign(form, { title: r.title, description: r.description ?? '', category: r.category, ownerId: r.ownerId, businessUnit: r.businessUnit, impact: r.impact, likelihood: r.likelihood, treatment: r.treatment, treatmentPlan: r.treatmentPlan ?? '', treatmentDueAt: r.treatmentDueAt ? new Date(r.treatmentDueAt).toISOString().slice(0, 10) : '', controlIds: r.controls.map(c => c.id) })
  }
}, { immediate: true })

const act = useAction()
async function save() {
  const body = { ...form, description: form.description || null, treatmentPlan: form.treatmentPlan || null, treatmentDueAt: form.treatmentDueAt || null }
  const ok = props.risk
    ? await act.run(() => api.patch(`/api/risks/${props.risk!.id}`, body), 'Risk updated')
    : await act.run(() => api.post('/api/risks', body), 'Risk added to register')
  if (ok) emit('saved')
}
</script>
