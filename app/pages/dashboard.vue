<template>
  <div class="page">
    <UiPageHeader title="Overview" :subtitle="data ? `Updated at ${fmtTime(data.generatedAt)}` : ''">
      <NuxtLink v-if="allowed('reports:board')" to="/reports/board-pack" class="btn"><i class="bi bi-file-earmark-text" aria-hidden="true" /> Board report</NuxtLink>
      <NuxtLink to="/alerts" class="btn btn-primary">Review alerts</NuxtLink>
    </UiPageHeader>

    <template v-if="data">
      <div class="kpi-grid">
        <UiKpiCard label="Critical alerts" :value="data.alerts.bySeverity.critical" :tone="data.alerts.bySeverity.critical ? 'critical' : 'ok'" to="/alerts?severity=critical"
          :sub="`${data.alerts.overdue} past SLA`" :trend="trendOf('critical')" />
        <UiKpiCard label="Open alerts" :value="data.alerts.open" to="/alerts"
          :sub="`${data.alerts.pendingClosure} awaiting sign-off`" />
        <UiKpiCard label="Rules passing" :value="data.ccm.pass" :suffix="`of ${data.ccm.total}`" :tone="data.ccm.fail ? 'high' : 'ok'" to="/ccm"
          :sub="`${data.ccm.fail} failing, ${data.ccm.warn} near threshold`" />
        <UiKpiCard v-if="allowed('aml:read')" label="Open AML cases" :value="data.aml.open" to="/aml"
          :sub="`${data.aml.sarPending} SARs to submit`" />
        <UiKpiCard label="Returns due in 30 days" :value="data.reports.due" :tone="data.reports.overdue ? 'critical' : data.reports.dueSoon ? 'high' : 'ok'" to="/compliance?tab=reports"
          :sub="`${data.reports.dueSoon} due this week`" />
      </div>

      <div class="dash-grid">
        <UiPanel title="Alerts raised" sub="Last 14 days">
          <ChartsTrendChart :data="data.trend" :series="trendSeries" aria-label="Daily alerts raised by severity over the last 14 days" />
        </UiPanel>

        <UiPanel title="Recent activity" flush>
          <ul class="feed">
            <li v-for="a in data.activity.slice(0, 10)" :key="a.id">
              <div class="small"><span style="font-weight:500">{{ a.actorName }}</span> <span class="text-2">{{ describe(a.action) }}</span> <span class="mono">{{ detailOf(a) }}</span></div>
              <div class="small muted">{{ fmtRelative(a.at) }}</div>
            </li>
          </ul>
          <div v-if="!data.activity.length" class="empty">No activity in the last 7 days</div>
        </UiPanel>
      </div>

      <div class="grid-3">
        <UiPanel title="Control monitoring" :sub="`${data.ccm.coverage}% of key controls monitored`" flush>
          <template #actions><NuxtLink to="/ccm" class="small">View all</NuxtLink></template>
          <table class="table">
            <tbody>
              <tr v-for="r in data.ccm.rules" :key="r.id">
                <td style="width:20px;padding-right:0"><UiLed :state="r.state" /></td>
                <td><span class="small">{{ r.name }}</span></td>
                <td class="small muted num" style="text-align:right">{{ r.breaches ? `${r.breaches} open` : '' }}</td>
              </tr>
            </tbody>
          </table>
        </UiPanel>

        <UiPanel v-if="allowed('risk:read')" title="Risks by category" :sub="`${data.risks.total} open risks`">
          <ChartsBarList :rows="data.risks.categories.map(c => ({ label: c.name, value: c.count, sub: c.critical ? `${c.critical} critical` : undefined }))" label="Open risks by category" />
          <dl class="bands">
            <div v-for="b in (['critical', 'high', 'moderate', 'low'] as const)" :key="b">
              <dt class="small muted">{{ labelize(b) }}</dt>
              <dd class="num">{{ data.risks.residualBands[b] }}</dd>
            </div>
          </dl>
        </UiPanel>

        <UiPanel v-if="allowed('risk:read')" title="Highest residual risks" flush>
          <template #actions><NuxtLink to="/risks" class="small">View register</NuxtLink></template>
          <table class="table">
            <tbody>
              <tr v-for="r in data.risks.top" :key="r.id" class="clickable" tabindex="0" @click="navigateTo(`/risks?id=${r.id}`)" @keydown.enter="navigateTo(`/risks?id=${r.id}`)">
                <td><div class="small">{{ r.title }}</div><div class="cell-sub mono">{{ r.ref }}</div></td>
                <td style="text-align:right"><UiPill :value="bandTone(r.band)" :text="`${r.residual}`" /></td>
              </tr>
            </tbody>
          </table>
          <p v-if="data.risks.controlGaps" class="small muted" style="padding:10px 16px;border-top:1px solid var(--border)">{{ data.risks.controlGaps }} risks have no mapped controls.</p>
        </UiPanel>
      </div>
    </template>
    <div v-else-if="error" class="callout danger">{{ errorMessage(error) }}</div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'dashboard:exec' })
useHead({ title: 'Overview' })

const { can: allowed } = useAccess()
const { data, error, refresh } = await useFetch('/api/dashboard')
useLiveRefresh(['alert', 'ccm', 'activity'], refresh, 1500)

const trendSeries = [
  { key: 'critical', label: 'Critical', color: 'var(--critical)' },
  { key: 'high', label: 'High', color: 'var(--warning)' },
  { key: 'medium', label: 'Medium', color: 'var(--info)' },
]
const trendOf = (k: 'critical' | 'high') => data.value?.trend.map(d => d[k]) ?? []
const bandTone = (b: string) => (b === 'moderate' ? 'medium' : b === 'low' ? 'ok' : b)

const ACTIONS: Record<string, string> = {
  'alert.created': 'raised', 'alert.escalated': 'escalated', 'alert.acknowledged': 'acknowledged', 'alert.closed': 'closed',
  'alert.closure_requested': 'requested closure of', 'alert.assigned': 'reassigned', 'ccm.rule_changed': 'changed rule', 'ccm.manual_run': 'ran control monitoring',
  'risk.created': 'added', 'risk.updated': 'updated', 'control.tested': 'tested', 'policy.approve': 'approved',
  'incident.logged': 'logged', 'csa.submitted': 'submitted self-assessment', 'transaction.screened': 'screened',
  'regulatory_report.submitted': 'submitted', 'customer.verify_kyc': 'verified KYC for', 'aml.sar_submit': 'submitted SAR for',
}
const describe = (action: string) => ACTIONS[action] ?? action.split('.').pop()!.replace(/_/g, ' ')
const detailOf = (a: { entityType: string, entityId: string | null, detail: Record<string, unknown> | null }) => {
  const d = a.detail ?? {}
  return String(d.ref ?? d.name ?? d.title ?? (a.entityId ? `#${a.entityId}` : ''))
}
</script>

<style scoped>
.feed { list-style: none; margin: 0; padding: 0; max-height: 340px; overflow-y: auto; }
.feed li { padding: 10px 16px; border-bottom: 1px solid var(--border); display: flex; flex-direction: column; gap: 2px; }
.feed li:last-child { border-bottom: none; }
.bands { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 18px 0 0; padding-top: 14px; border-top: 1px solid var(--border); }
.bands dd { margin: 0; font-size: 18px; font-weight: 600; }
</style>
