<template>
  <div class="page">
    <UiPageHeader title="Board report" :subtitle="data ? `Generated ${fmtDateTime(data.generatedAt)} by ${data.generatedBy}` : ''">
      <button class="btn" type="button" @click="refresh()"><i class="bi bi-arrow-repeat" aria-hidden="true" /> Regenerate</button>
      <button class="btn btn-primary" type="button" @click="print"><i class="bi bi-printer" aria-hidden="true" /> Print / save PDF</button>
    </UiPageHeader>

    <template v-if="data">
      <UiPanel title="Summary">
        <ol class="narrative">
          <li v-for="(n, i) in data.narrative" :key="i">{{ n }}</li>
        </ol>
      </UiPanel>

      <div class="kpi-grid">
        <UiKpiCard label="Open alerts" :value="data.kpis.openAlerts" :tone="data.kpis.criticalAlerts ? 'critical' : 'ok'" :sub="`${data.kpis.criticalAlerts} critical · ${data.kpis.overdueAlerts} past SLA`" />
        <UiKpiCard label="Mean time to close (30d)" :value="data.kpis.mttrHours ?? '-'" :suffix="data.kpis.mttrHours !== null ? 'h' : ''" tone="medium" />
        <UiKpiCard label="CCM rules passing" :value="data.kpis.rulesPassing" :suffix="`/${data.kpis.rulesTotal}`" :tone="data.kpis.rulesPassing < data.kpis.rulesTotal ? 'high' : 'ok'" />
        <UiKpiCard label="Obligations compliant" :value="data.kpis.complianceRate ?? '-'" :suffix="data.kpis.complianceRate !== null ? '%' : ''" tone="ok" />
        <UiKpiCard label="False-positive rate (30d)" :value="data.kpis.falsePositiveRate ?? '-'" :suffix="data.kpis.falsePositiveRate !== null ? '%' : ''" tone="neutral" />
        <UiKpiCard label="SARs submitted (30d)" :value="data.kpis.sarsSubmitted30d" tone="purple" />
      </div>

      <div class="grid-2">
        <UiPanel title="Top residual risks" flush>
          <UiDataTable :columns="[{ key: 'ref', label: 'Ref', width: '80px' }, { key: 'title', label: 'Risk' }, { key: 'scores', label: 'Residual (inherent)', width: '150px', sortable: false }, { key: 'owner', label: 'Owner', width: '140px' }]" :rows="data.topRisks" row-key="ref">
            <template #cell-ref="{ row }"><span class="mono">{{ row.ref }}</span></template>
            <template #cell-scores="{ row }"><span class="num">{{ row.residual }} <span class="muted">({{ row.inherent }})</span></span> <UiPill :value="row.band === 'moderate' ? 'medium' : row.band === 'low' ? 'ok' : row.band" :text="labelize(row.band)" /></template>
          </UiDataTable>
        </UiPanel>
        <UiPanel title="Controls in breach" flush>
          <UiDataTable :columns="[{ key: 'id', label: 'Rule', width: '90px' }, { key: 'name', label: 'Control monitor' }, { key: 'breaches', label: 'Breaches', width: '90px', align: 'right' }]" :rows="data.failingRules" empty="All monitored controls passing">
            <template #cell-id="{ row }"><span class="mono">{{ row.id }}</span></template>
          </UiDataTable>
        </UiPanel>
        <UiPanel title="Alerts past SLA" flush>
          <UiDataTable :columns="[{ key: 'ref', label: 'Ref', width: '120px' }, { key: 'title', label: 'Alert' }, { key: 'severity', label: 'Severity', width: '110px' }]" :rows="data.overdueAlerts" row-key="ref" empty="No alerts past SLA">
            <template #cell-ref="{ row }"><span class="mono small">{{ row.ref }}</span></template>
            <template #cell-severity="{ row }"><UiSeverity :value="row.severity" /></template>
          </UiDataTable>
        </UiPanel>
        <UiPanel title="Regulatory" flush>
          <UiDataTable :columns="[{ key: 'name', label: 'Return due (30 days)' }, { key: 'regulator', label: 'Regulator', width: '100px' }, { key: 'dueAt', label: 'Due', width: '120px' }]" :rows="data.reportsDue" row-key="name" empty="Nothing due">
            <template #cell-dueAt="{ row }"><span class="small">{{ fmtDate(row.dueAt) }}</span></template>
          </UiDataTable>
          <div v-if="data.nonCompliant.length" class="panel-body">
            <div class="section-title">Non-compliant obligations</div>
            <div v-for="o in data.nonCompliant" :key="o.ref" class="small"><span class="mono muted">{{ o.ref }}</span> {{ o.title }} <span class="muted">({{ o.framework }})</span></div>
          </div>
        </UiPanel>
      </div>

      <UiPanel title="Combined assurance progress">
        <div class="grid-3">
          <UiMeter v-for="a in data.assurance" :key="a.line" :value="a.total ? (100 * a.complete) / a.total : 0" :label="`Line ${a.line}`" :display="`${a.complete}/${a.total} complete`" :good="70" :warn="40" />
        </div>
      </UiPanel>
    </template>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'reports:board' })
useHead({ title: 'Board report' })
const { data, refresh } = await useFetch('/api/reports/board-pack')
const print = () => window.print()
</script>

<style scoped>
.narrative { margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; font-size: 14px; line-height: 1.6; }
</style>
