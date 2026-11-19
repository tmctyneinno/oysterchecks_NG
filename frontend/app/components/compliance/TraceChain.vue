<template>
  <section>
    <div class="section-title">Traceability</div>
    <p class="small muted" style="margin:0 0 10px">From the regulatory source to the assurance conclusion, using the same records as every other module.</p>
    <div v-if="!t" class="small muted">Loading…</div>
    <ol v-else class="chain">
      <li>
        <span class="step">Regulatory source</span>
        <div v-if="t.source" class="small">{{ t.source.title }} <span class="mono muted">{{ t.source.ref }}</span><div class="muted">{{ t.source.issuer }} · checked {{ fmtRelative(t.source.checkedAt) }}</div></div>
        <div v-else class="small muted">Internal requirement, no external source</div>
        <div v-if="t.changes.length" class="small">Changed by <NuxtLink v-for="c in t.changes" :key="c.id" :to="`/perimeter/changes?id=${c.id}`" class="mono" style="margin-right:6px">{{ c.ref }}</NuxtLink></div>
      </li>
      <li><span class="step">Obligation</span><div class="small"><span class="mono">{{ t.obligation.ref }}</span> {{ t.obligation.title }} <UiPill :value="t.obligation.status" /></div></li>
      <li><span class="step">Legal entities</span><div class="small">{{ t.entities.map((e: any) => e.name).join(', ') }}</div></li>
      <li><span class="step">Risks</span><UiChipList :items="t.risks" to="/risks?id=" label="title" class="small" /></li>
      <li>
        <span class="step">Controls</span>
        <div v-if="t.controls.length" class="stack" style="gap:4px">
          <div v-for="c in t.controls" :key="c.id" class="small row" style="gap:6px"><NuxtLink :to="`/controls?id=${c.id}`" class="mono">{{ c.ref }}</NuxtLink> {{ c.name }} <UiPill :value="c.effectiveness" /></div>
        </div>
        <div v-else class="small muted">None mapped</div>
      </li>
      <li>
        <span class="step">Monitoring rules</span>
        <div v-if="t.rules.length" class="row-wrap small"><span v-for="r in t.rules" :key="r.id" class="row" style="gap:4px"><UiLed :state="r.state" /> {{ r.id }} {{ r.name }}</span></div>
        <div v-else class="small muted">Not under continuous monitoring</div>
      </li>
      <li>
        <span class="step">Alerts and issues</span>
        <div v-if="t.alerts.length" class="stack" style="gap:4px"><NuxtLink v-for="a in t.alerts" :key="a.id" :to="`/alerts?id=${a.id}`" class="small"><span class="mono">{{ a.ref }}</span> {{ a.title }}</NuxtLink></div>
        <div v-else class="small muted">No open alerts</div>
      </li>
      <li>
        <span class="step">Actions</span>
        <div v-if="t.actions.length" class="stack" style="gap:4px"><div v-for="a in t.actions" :key="a.id" class="small">{{ a.title }} <span class="muted">· {{ a.ownerName }} · {{ a.status === 'done' ? 'done' : `due ${fmtDate(a.dueAt)}` }}</span></div></div>
        <div v-else class="small muted">None open</div>
      </li>
      <li><span class="step">Evidence</span><div class="small">{{ plural(t.evidence, 'file') }} on the obligation and its controls</div></li>
      <li class="conclusion">
        <span class="step">Assurance conclusion</span>
        <div class="row" style="gap:8px"><UiPill :value="t.conclusion" /><span class="small muted">{{ plural(t.assurance.length, 'assurance activity', 'assurance activities') }} cover these controls</span></div>
        <ul v-if="t.reasons.length" class="small text-2" style="margin:6px 0 0;padding-left:18px"><li v-for="r in t.reasons" :key="r">{{ r }}</li></ul>
      </li>
    </ol>
  </section>
</template>

<script setup lang="ts">
const props = defineProps<{ obligationId: number }>()
const t = ref<any>(null)
async function load() {
  t.value = null
  try { t.value = await $fetch<any>(`/api/trace/obligation/${props.obligationId}`) }
  catch { t.value = null }
}
watch(() => props.obligationId, load, { immediate: true })
useLiveRefresh(['data', 'activity'], load, 800)
</script>

<style scoped>
.chain { list-style: none; margin: 0; padding: 0 0 0 14px; border-left: 2px solid var(--border); display: flex; flex-direction: column; gap: 10px; }
.chain li { position: relative; display: flex; flex-direction: column; gap: 3px; }
.chain li::before { content: ''; position: absolute; left: -20px; top: 5px; width: 10px; height: 10px; border-radius: 50%; background: var(--surface); border: 2px solid var(--border-strong); }
.chain li.conclusion::before { border-color: var(--accent); background: var(--accent); }
.step { font-size: 12px; color: var(--muted); font-weight: 500; }
</style>
