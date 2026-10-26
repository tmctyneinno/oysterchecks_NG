<template>
  <UiModal :open="open" :title="isNew ? 'New CCM rule' : `Configure ${rule?.id}`" :submit-label="isNew ? 'Create rule' : 'Save changes'" :pending="act.pending.value" @close="emit('close')" @submit="save">
    <div v-if="!isNew" class="callout warn small"><i class="bi bi-shield-exclamation" aria-hidden="true" /> Rule changes are a control override. Your reason and the before/after values are written to the immutable audit log.</div>
    <UiField label="Name" required><input v-model="form.name" class="input"></UiField>
    <UiField label="Trigger description" required><input v-model="form.description" class="input"></UiField>
    <UiField v-if="isNew" label="Evaluator" hint="Rules reuse a vetted evaluator with your parameters." required>
      <select v-model="form.evaluator" class="select" @change="resetParams">
        <option v-for="(c, key) in catalogue" :key="key" :value="key">{{ c.label }}</option>
      </select>
    </UiField>
    <div v-if="spec" class="form-grid">
      <UiField v-for="(p, key) in spec.params" :key="key" :label="p.label" :hint="`${p.min}–${p.max}`">
        <input v-model.number="form.params[key]" type="number" :min="p.min" :max="p.max" class="input">
      </UiField>
    </div>
    <div class="form-grid">
      <UiField label="Severity">
        <select v-model="form.severity" class="select"><option v-for="s in SEVERITIES" :key="s" :value="s">{{ labelize(s) }}</option></select>
      </UiField>
      <UiField label="SLA type">
        <select v-model="form.sla.type" class="select">
          <option value="minutes">Minutes</option><option value="business_days">Business days</option>
          <option value="end_of_day">Same day</option><option value="immediate">Immediate</option><option value="before_entity_due">Before entity deadline</option>
        </select>
      </UiField>
      <UiField v-if="form.sla.type === 'minutes' || form.sla.type === 'before_entity_due'" label="SLA minutes"><input v-model.number="form.sla.minutes" type="number" min="0" class="input"></UiField>
      <UiField v-if="form.sla.type === 'business_days'" label="Business days"><input v-model.number="form.sla.days" type="number" min="1" class="input"></UiField>
    </div>
    <div v-if="isNew" class="form-grid">
      <UiField label="Module" required><input v-model="form.module" class="input"></UiField>
      <UiField label="Data source" required><input v-model="form.dataSource" class="input"></UiField>
    </div>
    <UiField label="Escalation path (in order)" required>
      <div class="chip-list">
        <label v-for="t in targets" :key="t.key" class="check chip">
          <input type="checkbox" :checked="form.escalationPath.includes(t.key)" @change="toggle(t.key)">{{ t.label }}
          <span v-if="form.escalationPath.includes(t.key)" class="num muted">{{ form.escalationPath.indexOf(t.key) + 1 }}</span>
        </label>
      </div>
    </UiField>
    <label v-if="!isNew" class="check"><input v-model="form.enabled" type="checkbox"> Rule enabled</label>
    <UiField v-if="!isNew" label="Reason for change" required><textarea v-model="form.reason" class="textarea" placeholder="e.g. Threshold tuned after Q3 false-positive review (ref. MRC-112)" /></UiField>
  </UiModal>
</template>

<script setup lang="ts">
interface Rule { id: string, name: string, description: string, evaluator: string, params: Record<string, number>, severity: Severity, sla: SlaSpec, escalationPath: string[], enabled: boolean, module: string, dataSource: string }
const props = defineProps<{ open: boolean, rule: Rule | null, isNew: boolean, catalogue: Record<string, { label: string, params: Record<string, { label: string, min: number, max: number }> }>, targets: { key: string, label: string }[] }>()
const emit = defineEmits<{ close: [], saved: [] }>()

const blank = () => ({ name: '', description: '', evaluator: Object.keys(props.catalogue)[0]!, params: {} as Record<string, number>, severity: 'high' as Severity, sla: { type: 'minutes', minutes: 240, days: 1 } as any, escalationPath: ['COMPLIANCE', 'CCO'] as string[], enabled: true, module: '', dataSource: '', reason: '' })
const form = reactive(blank())
watch(() => [props.open, props.rule], () => {
  if (!props.open) return
  Object.assign(form, blank())
  if (props.rule && !props.isNew) Object.assign(form, structuredClone(toRaw({ ...props.rule, sla: { minutes: 240, days: 1, ...props.rule.sla }, params: { ...props.rule.params } })), { reason: '' })
  else resetParams()
}, { immediate: true })

const spec = computed(() => props.catalogue[form.evaluator])
function resetParams() {
  form.params = Object.fromEntries(Object.entries(spec.value?.params ?? {}).map(([k, p]) => [k, p.min]))
}
const toggle = (k: string) => { form.escalationPath = form.escalationPath.includes(k) ? form.escalationPath.filter(x => x !== k) : [...form.escalationPath, k] }

const slaPayload = () => {
  const s = form.sla
  return s.type === 'minutes' || s.type === 'before_entity_due' ? { type: s.type, minutes: s.minutes } : s.type === 'business_days' ? { type: s.type, days: s.days } : { type: s.type }
}

const act = useAction()
async function save() {
  const base = { name: form.name, description: form.description, params: form.params, severity: form.severity, sla: slaPayload(), escalationPath: form.escalationPath }
  const ok = props.isNew
    ? await act.run(() => api.post('/api/ccm/rules', { ...base, evaluator: form.evaluator, module: form.module, dataSource: form.dataSource }), 'Rule created and evaluated')
    : await act.run(() => api.patch(`/api/ccm/rules/${props.rule!.id}`, { ...base, enabled: form.enabled, reason: form.reason }), 'Rule updated')
  if (ok) emit('saved')
}
</script>
