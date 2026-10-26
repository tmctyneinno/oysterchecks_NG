<template>
  <div class="page">
    <UiPageHeader title="Integrations" subtitle="Data sources connected to the platform" />

    <div v-if="data?.demoSimulator" class="callout warn">
      <i class="bi bi-cone-striped" aria-hidden="true" />
      <div class="small"><strong>Demo data simulator is on.</strong> Connectors marked “simulated” are fed by a built-in generator so CCM has live data to evaluate. Set <code>NUXT_DEMO_SIMULATOR=false</code> and implement the connector adapters before any real deployment.</div>
    </div>

    <div class="int-grid">
      <article v-for="i in data?.integrations ?? []" :key="i.id" class="panel int">
        <div class="row" style="align-items:flex-start">
          <div style="min-width:0"><h2 style="font-size:15px">{{ i.name }}</h2><div class="small muted">{{ i.method }} · {{ i.direction }}</div></div>
          <span class="spacer" />
          <UiPill :value="i.status === 'connected' || i.status === 'simulated' ? 'ok' : i.status === 'degraded' ? 'high' : 'critical'" :text="labelize(i.status)" />
        </div>
        <dl class="kv small" style="grid-template-columns:110px 1fr">
          <dt>Frequency</dt><dd>{{ i.frequency }}</dd>
          <dt>Data elements</dt><dd>{{ i.dataElements }}</dd>
          <dt>Last sync</dt><dd>{{ i.lastSyncAt ? fmtRelative(i.lastSyncAt) : 'Never' }}</dd>
          <dt>Records</dt><dd class="num">{{ fmtNumber(i.recordsIngested) }}</dd>
          <dt>Data quality</dt><dd><UiMeter v-if="i.dataQuality !== null" :value="i.dataQuality" :display="`${i.dataQuality}%`" :good="99" :warn="97" /><span v-else class="muted">-</span></dd>
        </dl>
        <button v-if="allowed('integrations:manage')" class="btn btn-sm" type="button" style="align-self:flex-start" @click="sync(i.id)"><i class="bi bi-arrow-repeat" aria-hidden="true" /> Sync now</button>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'integrations:read' })
useHead({ title: 'Integrations' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/integrations')
useLiveRefresh(['data'], refresh, 5000)
const act = useAction()
async function sync(id: string) {
  if (await act.run(() => api.post(`/api/integrations/${id}/sync`), 'Sync requested')) refresh()
}
</script>

<style scoped>
.int-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px; }
.int { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
</style>
