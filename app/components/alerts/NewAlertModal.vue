<template>
  <UiModal :open="open" title="Raise a manual alert" submit-label="Raise alert" :pending="act.pending.value" @close="emit('close')" @submit="submit">
    <UiField label="Title" required><input v-model="form.title" class="input" maxlength="200"></UiField>
    <UiField label="Detail"><textarea v-model="form.detail" class="textarea" /></UiField>
    <div class="form-grid">
      <UiField label="Severity" required>
        <select v-model="form.severity" class="select"><option v-for="s in SEVERITIES" :key="s" :value="s">{{ labelize(s) }}</option></select>
      </UiField>
      <UiField label="Resolve within (hours)" required><input v-model.number="form.slaHours" type="number" min="0.25" step="0.25" class="input"></UiField>
      <UiField label="Module" required>
        <select v-model="form.module" class="select"><option v-for="m in modules" :key="m">{{ m }}</option></select>
      </UiField>
      <UiField v-if="roleDef?.scope !== 'bu'" label="Business unit">
        <select v-model="form.businessUnit" class="select"><option :value="null">None</option><option v-for="b in BUSINESS_UNITS" :key="b">{{ b }}</option></select>
      </UiField>
    </div>
    <UiField label="Escalation path" hint="First step is routed immediately; later steps on SLA breach." required>
      <div class="chip-list">
        <label v-for="(t, key) in ESCALATION_TARGETS" :key="key" class="check chip">
          <input type="checkbox" :checked="form.escalationPath.includes(key)" @change="toggle(key)">{{ t.label }}
          <span v-if="form.escalationPath.includes(key)" class="num muted">{{ form.escalationPath.indexOf(key) + 1 }}</span>
        </label>
      </div>
    </UiField>
  </UiModal>
</template>

<script setup lang="ts">
defineProps<{ open: boolean }>()
const emit = defineEmits<{ close: [], created: [id: number] }>()
const { roleDef } = useAccess()
const modules = ['AML / KYC', 'Transaction Monitoring', 'Controls Library', 'Regulatory Reporting', 'IAM / Security', 'Workforce Vetting', 'Third-Party Risk', 'Combined Assurance', 'Other']
const blank = () => ({ title: '', detail: '', severity: 'high' as Severity, slaHours: 4, module: 'Controls Library', businessUnit: null as string | null, escalationPath: ['COMPLIANCE', 'CCO'] as EscalationTarget[] })
const form = reactive(blank())
const toggle = (k: EscalationTarget) => { form.escalationPath = form.escalationPath.includes(k) ? form.escalationPath.filter(x => x !== k) : [...form.escalationPath, k] }
const act = useAction()
async function submit() {
  const row = await act.run(() => api.post<{ id: number }>('/api/alerts', form), 'Alert raised and routed')
  if (row) { Object.assign(form, blank()); emit('created', row.id) }
}
</script>
