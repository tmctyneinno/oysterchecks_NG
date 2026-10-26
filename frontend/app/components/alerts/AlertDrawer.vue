<template>
  <UiDrawer :open="!!id" :title="alert?.title ?? 'Alert'" :eyebrow="alert ? `${alert.ref} · ${alert.rule?.id ?? (alert.source === 'kri' ? 'KRI' : alert.source === 'manual' ? 'Manual' : 'Monitoring')}` : ''" :loading="pending && !alert" @close="emit('close')">
    <template v-if="alert" #meta>
      <UiSeverity :value="alert.severity" />
      <UiPill :value="alert.status" />
      <UiPill v-if="alert.overdue" value="overdue" text="SLA breached" icon="bi-clock-history" />
      <UiPill v-if="alert.falsePositive" tone="neutral" text="False positive" />
    </template>

    <template v-if="alert">
      <p v-if="alert.detail" class="text-2">{{ alert.detail }}</p>

      <section>
        <div class="section-title">Escalation path</div>
        <ol class="steps">
          <li v-for="(s, i) in alert.escalationSteps" :key="i" :class="{ reached: s.reached, current: s.current }">
            <span class="step-dot" aria-hidden="true" />{{ s.label }}<span v-if="s.current" class="sr-only"> (current level)</span>
          </li>
        </ol>
      </section>

      <dl class="kv">
        <dt>Assignee</dt><dd>{{ alert.assigneeName ?? 'Unassigned' }}</dd>
        <dt>Detected</dt><dd class="mono">{{ fmtDateTime(alert.detectedAt) }}</dd>
        <dt>Acknowledge by</dt><dd class="mono">{{ fmtDateTime(alert.ackDueAt) }} <span v-if="alert.acknowledgedAt" class="muted">(acknowledged {{ fmtDateTime(alert.acknowledgedAt) }})</span></dd>
        <dt>Resolve by</dt><dd class="mono">{{ fmtDateTime(alert.dueAt) }} <span class="muted">({{ alert.slaLabel }})</span></dd>
        <dt>Module</dt><dd>{{ alert.module }}<span v-if="alert.businessUnit" class="muted">, {{ alert.businessUnit }}</span></dd>
        <template v-if="alert.rule"><dt>Rule</dt><dd>{{ alert.rule.name }}. <span class="muted">{{ alert.rule.description }}</span></dd>
          <dt>Data source</dt><dd>{{ alert.rule.dataSource }} <span class="muted"></span></dd></template>
        <template v-if="alert.remediationPlan"><dt>Remediation plan</dt><dd>{{ alert.remediationPlan }}</dd></template>
        <template v-if="alert.closureEvidence"><dt>Closure evidence</dt><dd>{{ alert.closureEvidence }}</dd></template>
        <template v-if="alert.closedAt"><dt>Closed</dt><dd>{{ fmtDateTime(alert.closedAt) }} by {{ alert.closedByName }}</dd></template>
      </dl>

      <div v-if="alert.status === 'pending_closure'" class="callout info">
        <i class="bi bi-person-check" aria-hidden="true" />
        <span v-if="alert.canSignOff">Closure has been requested. Review the evidence and sign off or reject.</span>
        <span v-else>Closure requested. A different authorised user must sign it off.</span>
      </div>

      <UiEvidence entity-type="alert" :entity-id="alert.id" :can-upload="allowed('alerts:act') && alert.status !== 'closed'" />

      <section>
        <div class="section-title">Timeline</div>
        <ul class="timeline">
          <li v-for="e in [...alert.events].reverse()" :key="e.id">
            <span class="dot" :style="{ background: eventColor(e.type) }" aria-hidden="true" />
            <div>
              <div class="small"><strong>{{ labelize(e.type) }}</strong> · {{ e.actorName }} <span class="muted mono">{{ fmtDateTime(e.at) }}</span></div>
              <div v-if="e.note" class="small text-2" style="white-space:pre-wrap">{{ e.note }}</div>
            </div>
          </li>
        </ul>
      </section>
    </template>

    <template v-if="alert && alert.status !== 'closed'" #footer>
      <template v-if="allowed('alerts:act') && alert.status !== 'pending_closure'">
        <button v-if="!alert.acknowledgedAt" class="btn btn-primary" type="button" :disabled="act.pending.value" @click="quick('acknowledge')">Acknowledge</button>
        <button class="btn" type="button" @click="dialog = 'assign'">Assign</button>
        <button class="btn" type="button" @click="dialog = 'remediation'">Remediation plan</button>
        <button class="btn" type="button" @click="dialog = 'note'">Add note</button>
        <button v-if="alert.escalationLevel < alert.escalationPath.length - 1" class="btn" type="button" @click="dialog = 'escalate'">Escalate</button>
        <button v-if="allowed('incidents:write')" class="btn btn-ghost" type="button" @click="dialog = 'incident'">Raise incident</button>
        <span class="spacer" />
        <button class="btn btn-primary" type="button" @click="dialog = 'request-closure'">Request closure</button>
      </template>
      <template v-if="alert.canSignOff">
        <button class="btn btn-danger" type="button" @click="dialog = 'reject'">Reject closure</button>
        <span class="spacer" />
        <button class="btn btn-primary" type="button" @click="dialog = 'approve'">Sign off closure</button>
      </template>
    </template>
  </UiDrawer>

  <UiModal :open="dialog === 'assign'" title="Assign alert" submit-label="Assign" :pending="act.pending.value" :disabled="!form.assigneeId" @close="dialog = null" @submit="send('assign', { assigneeId: form.assigneeId })">
    <UiField label="Assignee" required><UiUserSelect v-model="form.assigneeId" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'note'" title="Add investigation note" :pending="act.pending.value" @close="dialog = null" @submit="send('note', { note: form.text })">
    <UiField label="Note" required><textarea v-model="form.text" class="textarea" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'remediation'" title="Remediation plan" submit-label="Submit plan" :pending="act.pending.value" @close="dialog = null" @submit="send('remediation', { plan: form.text })">
    <UiField label="Root cause and remediation steps" hint="Submitting a plan moves the alert to Investigating." required><textarea v-model="form.text" class="textarea" rows="5" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'escalate'" title="Escalate to next level" submit-label="Escalate" :pending="act.pending.value" @close="dialog = null" @submit="send('escalate', { reason: form.text })">
    <UiField label="Reason" required><textarea v-model="form.text" class="textarea" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'request-closure'" title="Request closure" submit-label="Request sign-off" :pending="act.pending.value" @close="dialog = null" @submit="send('request-closure', { evidence: form.text, falsePositive: form.falsePositive })">
    <p class="small text-2">Closure needs sign-off from the CCO or another authorised user who did not request it.</p>
    <UiField label="Closure evidence" hint="What was done, and where the evidence is (attach files in the drawer)." required><textarea v-model="form.text" class="textarea" rows="5" /></UiField>
    <label class="check"><input v-model="form.falsePositive" type="checkbox"> This alert was a false positive (feeds tuning metrics)</label>
  </UiModal>
  <UiModal :open="dialog === 'approve'" title="Sign off closure" submit-label="Close alert" :pending="act.pending.value" @close="dialog = null" @submit="send('sign-off', { decision: 'approve', note: form.text || undefined })">
    <UiField label="Sign-off note (optional)"><textarea v-model="form.text" class="textarea" /></UiField>
  </UiModal>
  <UiModal :open="dialog === 'reject'" title="Reject closure" submit-label="Reject" danger :pending="act.pending.value" @close="dialog = null" @submit="send('sign-off', { decision: 'reject', note: form.text })">
    <UiField label="What is missing?" required><textarea v-model="form.text" class="textarea" /></UiField>
  </UiModal>
  <IncidentsNewIncidentModal
    v-if="alert"
    :open="dialog === 'incident'"
    :preset="{ title: alert.title, description: alert.detail ?? '', severity: alert.severity, businessUnit: alert.businessUnit, sourceAlertId: alert.id }"
    @close="dialog = null"
    @created="dialog = null; refresh(); emit('changed')"
  />
</template>

<script setup lang="ts">
const props = defineProps<{ id: number | null }>()
const emit = defineEmits<{ close: [], changed: [] }>()
const { can: allowed } = useAccess()

const { data: alert, pending, refresh } = useFetch(() => `/api/alerts/${props.id}`, { immediate: false, watch: false })
watch(() => props.id, (id) => { if (id) refresh() }, { immediate: true })
useLiveRefresh(['alert'], () => props.id && refresh())

const dialog = ref<string | null>(null)
const form = reactive({ assigneeId: null as number | null, text: '', falsePositive: false })
watch(dialog, () => { form.text = ''; form.falsePositive = false; form.assigneeId = alert.value?.assigneeId ?? null })

const act = useAction()
async function send(action: string, body: object = {}) {
  const ok = await act.run(() => api.post(`/api/alerts/${props.id}/${action}`, body), 'Alert updated')
  if (ok) {
    dialog.value = null
    await refresh()
    emit('changed')
  }
}
const quick = (action: string) => send(action)

const eventColor = (t: string) => t === 'escalated' || t === 'board_notified' ? 'var(--warning)' : t === 'closed' ? 'var(--accent)' : t === 'detected' ? 'var(--critical)' : t === 'closure_rejected' ? 'var(--critical)' : 'var(--info)'
</script>

<style scoped>
.steps { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
.steps li { display: inline-flex; align-items: center; gap: 6px; padding: 3px 9px; border-radius: var(--r-sm); border: 1px solid var(--border); font-size: 13px; color: var(--muted); }
.steps li.reached { color: var(--text); border-color: var(--border-strong); }
.steps li.current { background: var(--warning-soft); color: var(--warning-text); border-color: transparent; font-weight: 500; }
.step-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
</style>
