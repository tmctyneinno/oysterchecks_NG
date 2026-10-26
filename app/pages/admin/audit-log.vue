<template>
  <div class="page">
    <UiPageHeader title="Audit log" subtitle="Every user action and system event, retained for 7 years">
      <button class="btn" type="button" :disabled="verifying" @click="verify"><i class="bi bi-patch-check" aria-hidden="true" /> {{ verifying ? 'Verifying' : 'Verify integrity' }}</button>
    </UiPageHeader>

    <div v-if="verification" class="callout" :class="verification.valid ? 'info' : 'danger'" role="status">
      <i :class="['bi', verification.valid ? 'bi-shield-check' : 'bi-shield-exclamation']" aria-hidden="true" />
      <span v-if="verification.valid">All {{ fmtNumber(verification.checked) }} entries verified. No record has been altered or removed.</span>
      <span v-else>Chain broken at entry #{{ verification.brokenAt }}. Records from that point cannot be trusted. Escalate to the CISO.</span>
    </div>

    <div class="row-wrap">
      <input v-model="q" class="input" style="max-width:320px" placeholder="Filter by action, user or record" aria-label="Filter audit log">
    </div>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="rows">
        <template #cell-id="{ row }"><span class="mono small muted">#{{ row.id }}</span></template>
        <template #cell-at="{ row }"><span class="mono small">{{ new Date(row.at).toLocaleString('en-GB') }}</span></template>
        <template #cell-action="{ row }"><span class="mono small">{{ row.action }}</span></template>
        <template #cell-entity="{ row }"><span class="small">{{ row.entityType }}<span v-if="row.entityId" class="muted"> #{{ row.entityId }}</span></span></template>
        <template #cell-detail="{ row }"><code class="small muted detail">{{ row.detail ? JSON.stringify(row.detail) : '' }}</code></template>
        <template #cell-hash="{ row }"><code class="small muted" :title="row.hash">{{ row.hash.slice(0, 10) }}…</code></template>
      </UiDataTable>
      <div v-if="nextBefore" class="panel-body" style="text-align:center"><button class="btn btn-sm" type="button" @click="loadMore">Load older entries</button></div>
    </UiPanel>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'auditlog:read' })
useHead({ title: 'Audit log' })
const q = ref('')
type Row = { id: number, at: string, actorName: string, action: string, entityType: string, entityId: string | null, detail: Record<string, unknown> | null, ip: string | null, hash: string }
const rows = ref<Row[]>([])
const nextBefore = ref<number | null>(null)

// useRequestFetch forwards the session cookie during SSR; plain $fetch would not.
const requestFetch = useRequestFetch()
async function load(reset = true) {
  const res = await requestFetch<{ rows: Row[], nextBefore: number | null }>('/api/admin/audit-log', { query: { q: q.value || undefined, before: reset ? undefined : nextBefore.value ?? undefined } })
  rows.value = reset ? res.rows : [...rows.value, ...res.rows]
  nextBefore.value = res.nextBefore
}
const loadMore = () => load(false)
await load()
let t: ReturnType<typeof setTimeout>
watch(q, () => { clearTimeout(t); t = setTimeout(() => load(), 250) })

const columns = [
  { key: 'id', label: '#', width: '70px' },
  { key: 'at', label: 'Time', width: '170px' },
  { key: 'actorName', label: 'Actor', width: '170px' },
  { key: 'action', label: 'Action', width: '220px' },
  { key: 'entity', label: 'Entity', width: '150px', sortable: false },
  { key: 'detail', label: 'Detail', sortable: false },
  { key: 'hash', label: 'Hash', width: '110px', sortable: false },
]

const verifying = ref(false)
const verification = ref<{ valid: boolean, brokenAt: number | null, checked: number } | null>(null)
async function verify() {
  verifying.value = true
  // Plain string URL: Nitro's route-type inference exceeds TS's depth limit here.
  const url: string = '/api/admin/audit-log/verify'
  try { verification.value = await $fetch<NonNullable<typeof verification.value>>(url) }
  catch (e) { useToast().error(errorMessage(e)) }
  finally { verifying.value = false }
}
</script>

<style scoped>
.detail { display: block; max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
