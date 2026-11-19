<template>
  <div class="page">
    <UiPageHeader title="Perimeter intelligence" subtitle="Regulatory coverage across jurisdictions and legal entities">
      <NuxtLink to="/perimeter/changes" class="btn btn-primary"><i class="bi bi-arrow-left-right" aria-hidden="true" /> Regulatory change</NuxtLink>
    </UiPageHeader>

    <div class="callout">
      <i class="bi bi-info-circle" aria-hidden="true" />
      <div class="small">Content packs in this demonstration name real instruments, but summaries are simplified and illustrative. In production each pack is curated, version-controlled and validated before use.</div>
    </div>

    <div v-if="data" class="kpi-grid">
      <UiKpiCard label="Jurisdiction packs" :value="data.kpis.activePacks" tone="purple" :sub="`${data.kpis.availablePacks} more available`" />
      <UiKpiCard label="Regulatory sources" :value="data.kpis.sources" :sub="`${data.kpis.obligations} obligations mapped`" />
      <UiKpiCard label="Open changes" :value="data.kpis.openChanges" :tone="data.kpis.awaitingApproval ? 'high' : 'medium'" to="/perimeter/changes" :sub="`${data.kpis.awaitingApproval} awaiting approval`" />
      <UiKpiCard label="Awaiting validation" :value="data.kpis.unvalidated" :tone="data.kpis.unvalidated ? 'high' : 'ok'" sub="Extracted requirements a person must check" />
      <UiKpiCard label="Content freshness" :value="data.kpis.freshness" suffix="days" :tone="data.kpis.freshness > 14 ? 'high' : 'ok'" sub="Average since sources were last checked" />
      <UiKpiCard label="Content quality" :value="data.kpis.quality" suffix="%" :tone="data.kpis.quality < 95 ? 'high' : 'ok'" :sub="`${data.kpis.coverage}% coverage of active packs`" />
    </div>

    <UiTabs v-model="tab" :tabs="[{ key: 'coverage', label: 'Coverage' }, { key: 'sources', label: 'Sources', count: sources.length }, { key: 'comparison', label: 'Cross-border comparison', count: comparisons.length }]" />

    <template v-if="tab === 'coverage' && data">
      <UiPanel title="Jurisdiction coverage" sub="Active packs feed the obligation register; available packs can be switched on per legal entity">
        <ul class="packs">
          <li v-for="p in data.packs" :key="p.code" class="pack" :class="[p.status, { attention: p.openChanges }]">
            <div class="row" style="gap:8px">
              <span class="mono code">{{ p.code }}</span>
              <span class="pack-name">{{ p.name }}</span>
              <span class="spacer" />
              <UiPill :value="p.status" />
            </div>
            <template v-if="p.status === 'active'">
              <UiMeter :value="p.coverage" label="Coverage" :display="`${p.coverage}%`" :good="95" :warn="80" />
              <dl class="kv small">
                <dt>Regulators</dt><dd>{{ p.regulators.join(', ') }}</dd>
                <dt>Suspicious reports</dt><dd>{{ p.suspiciousReport }}</dd>
                <dt>Data breaches</dt><dd>{{ p.breachNotice }}</dd>
                <dt>Local settings</dt><dd>{{ p.language }} · {{ p.currency }} · {{ p.timeZone }}</dd>
                <dt>Data residency</dt><dd>{{ p.dataResidency }}</dd>
                <dt>Content</dt><dd>{{ plural(p.sources, 'source') }}, {{ plural(p.obligations, 'obligation') }}, checked {{ fmtRelative(p.lastReviewedAt) }}</dd>
              </dl>
              <div v-if="p.openChanges" class="small"><i class="bi bi-arrow-left-right" aria-hidden="true" /> {{ plural(p.openChanges, 'open change') }}</div>
            </template>
            <p v-else class="small muted">Regulators: {{ p.regulators.join(', ') }}. Not switched on for any entity.</p>
          </li>
        </ul>
      </UiPanel>

      <div class="two-col">
        <UiPanel title="Legal entities" sub="Exposure by entity and jurisdiction" flush>
          <UiDataTable :columns="entityColumns" :rows="data.entities">
            <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub"><span class="mono">{{ row.jurisdiction }}</span> · {{ row.regulators.join(', ') }}</div></template>
            <template #cell-openChanges="{ row }"><span class="num" :class="{ warn: row.openChanges }">{{ row.openChanges }}</span></template>
            <template #cell-exposure="{ row }"><span class="small">{{ plural(row.openAlerts, 'alert') }}, {{ plural(row.openCases, 'case') }}, {{ plural(row.highRisks, 'high risk') }}</span></template>
            <template #cell-assurance="{ row }"><UiPill :value="row.assurance" /></template>
          </UiDataTable>
        </UiPanel>

        <UiPanel title="Regulatory horizon" sub="Open changes and future effective dates" flush>
          <ul class="horizon">
            <li v-for="c in data.horizon" :key="c.id">
              <NuxtLink :to="`/perimeter/changes?id=${c.id}`" class="horizon-link">
                <div class="row" style="gap:8px"><span class="mono small">{{ c.ref }}</span><UiPill :value="c.status" /><span class="spacer" /><span class="mono small muted">{{ c.jurisdiction }}</span></div>
                <div class="small">{{ c.title }}</div>
                <div class="small muted">{{ c.effectiveAt ? `Effective ${fmtDate(c.effectiveAt)} (${fmtRelative(c.effectiveAt)})` : `Published ${fmtDate(c.publishedAt)}` }}</div>
              </NuxtLink>
            </li>
          </ul>
        </UiPanel>
      </div>
    </template>

    <UiPanel v-if="tab === 'sources'" flush title="Regulatory source inventory" sub="Every obligation traces back to an authoritative source, with when it was retrieved and a fingerprint of the version used">
      <UiDataTable :columns="sourceColumns" :rows="sources">
        <template #cell-title="{ row }"><div class="cell-title">{{ row.title }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.issuer }} · {{ labelize(row.type) }}</div></template>
        <template #cell-pack="{ row }"><span class="small">{{ row.pack }}</span></template>
        <template #cell-provenance="{ row }">
          <div class="small">{{ row.url }} · {{ row.version }}</div>
          <div class="cell-sub">Checked {{ fmtRelative(row.checkedAt) }} · <code :title="`SHA-256 ${row.sha256}`">{{ row.sha256.slice(0, 10) }}…</code></div>
        </template>
        <template #cell-obligations="{ row }">
          <span v-if="row.obligations.length" class="small"><NuxtLink v-for="(o, i) in row.obligations" :key="o.id" :to="`/compliance?id=${o.id}`" class="mono">{{ o.ref }}{{ Number(i) < row.obligations.length - 1 ? ', ' : '' }}</NuxtLink></span>
          <span v-else class="small muted">Not mapped yet</span>
        </template>
      </UiDataTable>
    </UiPanel>

    <template v-if="tab === 'comparison'">
      <p class="small text-2">How the same requirement differs by jurisdiction, and whether an existing control can be reused or needs a local variant.</p>
      <UiPanel v-for="c in comparisons" :key="c.topic" :title="c.topic" :sub="c.control ? `Group control: ${c.control.ref} ${c.control.name}` : 'No group control yet'" flush>
        <template v-if="c.control" #actions><UiPill :value="c.control.effectiveness" /></template>
        <UiDataTable :columns="comparisonColumns" :rows="c.rows" row-key="code">
          <template #cell-jurisdiction="{ row }"><span class="mono small">{{ row.code }}</span> <span class="small">{{ row.jurisdiction }}</span></template>
          <template #cell-requirement="{ row }"><span class="small">{{ row.requirement }}</span></template>
          <template #cell-reuse="{ row }"><UiPill :value="row.reuse" :text="REUSE[row.reuse]" /></template>
        </UiDataTable>
      </UiPanel>
    </template>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'perimeter:read' })
useHead({ title: 'Perimeter intelligence' })
const route = useRoute()
const { data, refresh } = await useFetch('/api/perimeter/overview')
const { data: sourceData } = await useFetch('/api/perimeter/sources', { default: () => [] })
const { data: comparisonData } = await useFetch('/api/perimeter/comparisons', { default: () => [] })
useLiveRefresh(['data'], refresh, 1000)

const sources = computed(() => sourceData.value)
const comparisons = computed(() => comparisonData.value)
const tab = ref(['sources', 'comparison'].includes(String(route.query.tab)) ? String(route.query.tab) : 'coverage')

const REUSE: Record<string, string> = { reused: 'Control reused', local_variant: 'Local variant needed', gap: 'Gap: no control', not_applicable: 'Not in scope' }
const entityColumns = [
  { key: 'name', label: 'Entity' },
  { key: 'openChanges', label: 'Changes', width: '90px', align: 'right' as const },
  { key: 'exposure', label: 'Exposure', sortable: false },
  { key: 'assurance', label: 'Assurance', width: '170px' },
]
const sourceColumns = [
  { key: 'title', label: 'Source' },
  { key: 'pack', label: 'Pack', width: '170px' },
  { key: 'provenance', label: 'Provenance', width: '280px', sortable: false },
  { key: 'obligations', label: 'Obligations', width: '150px', sortable: false },
]
const comparisonColumns = [
  { key: 'jurisdiction', label: 'Jurisdiction', width: '220px' },
  { key: 'requirement', label: 'Requirement', sortable: false },
  { key: 'reuse', label: 'Control reuse', width: '190px' },
]
</script>

<style scoped>
.packs { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; }
.pack { border: 1px solid var(--border); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 10px; background: var(--surface); }
.pack.available { background: var(--surface-2); }
.pack.attention { border-color: var(--warning); }
.code { font-weight: 600; }
.pack-name { font-weight: 500; }
.pack .kv { grid-template-columns: 112px 1fr; }
.two-col { display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); gap: 16px; }
@media (max-width: 1100px) { .two-col { grid-template-columns: minmax(0, 1fr); } }
.horizon { list-style: none; margin: 0; padding: 0; }
.horizon li + li { border-top: 1px solid var(--border); }
.horizon-link { display: flex; flex-direction: column; gap: 3px; padding: 10px 16px; color: var(--text); }
.horizon-link:hover { background: var(--surface-2); text-decoration: none; }
.warn { color: var(--warning); font-weight: 600; }
</style>
