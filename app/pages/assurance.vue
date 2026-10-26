<template>
  <div class="page">
    <UiPageHeader title="Assurance" subtitle="First, second and third line activity in one place">
      <button v-if="allowed('assurance:write')" class="btn btn-primary" type="button" @click="formOpen = true"><i class="bi bi-plus-lg" aria-hidden="true" /> Plan activity</button>
    </UiPageHeader>

    <template v-if="data">
      <div class="lines">
        <section v-for="l in data.lineSummary" :key="l.line" class="panel line">
          <div class="panel-head">
            <div>
              <div class="small muted">Line {{ l.line }}</div>
              <div class="panel-title">{{ lineName[l.line] }}</div>
            </div>
          </div>
          <div class="panel-body stack">
            <UiMeter :value="l.coverage" label="Key-control coverage" />
            <UiMeter :value="l.total ? (100 * l.complete) / l.total : 0" label="Plan complete" :display="`${l.complete}/${l.total}`" :good="70" :warn="40" />
            <div class="small text-2">{{ l.inProgress }} in progress · {{ l.satisfactory }}/{{ l.rated }} rated satisfactory</div>
            <div v-if="l.line === 1 && data.csaCompletion !== null" class="small text-2">Current-period CSA completion: <span class="num">{{ data.csaCompletion }}%</span> </div>
            <ul class="acts">
              <li v-for="a in data.activities.filter(x => x.line === l.line)" :key="a.id">
                <button type="button" class="act" @click="editing = a">
                  <span class="small" style="flex:1;min-width:0">{{ a.title }}</span>
                  <UiPill :value="a.rating ?? a.status" />
                </button>
              </li>
            </ul>
          </div>
        </section>
      </div>

      <UiPanel title="Assurance coverage map" :sub="`Key controls: ${data.gaps} with no assurance, ${data.overAssured} possibly duplicated`" flush>
        <UiDataTable :columns="columns" :rows="data.coverage" row-key="controlId">
          <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub"><span class="mono">{{ row.ref }}</span> · {{ row.businessUnit }}</div></template>
          <template #cell-l1="{ row }"><UiPill :value="row.lines[1] ? 'pass' : 'neutral'" :text="row.lines[1] ? 'Covered' : 'Not covered'" /></template>
          <template #cell-l2="{ row }"><UiPill :value="row.lines[2] ? 'pass' : 'neutral'" :text="row.lines[2] ? 'Covered' : 'Not covered'" /></template>
          <template #cell-l3="{ row }"><UiPill :value="row.lines[3] ? 'pass' : 'neutral'" :text="row.lines[3] ? 'Covered' : 'Not covered'" /></template>
          <template #cell-status="{ row }"><UiPill :value="row.status" :text="{ gap: 'No assurance', thin: 'One line', adequate: 'Adequate', over: 'Duplicated' }[row.status]" /></template>
          <template #cell-effectiveness="{ row }"><UiPill :value="row.effectiveness" /></template>
        </UiDataTable>
      </UiPanel>
    </template>

    <AssuranceActivityForm :open="formOpen" @close="formOpen = false" @saved="formOpen = false; refresh()" />

    <UiModal :open="!!editing" :title="editing?.title ?? ''" submit-label="Update" :pending="act.pending.value" @close="editing = null" @submit="updateActivity">
      <template v-if="editing">
        <p class="small text-2">{{ labelize(editing.provider) }} · {{ labelize(editing.activityType) }} · {{ fmtDate(editing.plannedStart) }} – {{ fmtDate(editing.plannedEnd) }} · {{ editing.ownerName }}</p>
        <div class="form-grid">
          <UiField label="Status"><select v-model="edit.status" class="select" :disabled="!allowed('assurance:write')"><option v-for="s in ['planned', 'in_progress', 'complete']" :key="s" :value="s">{{ labelize(s) }}</option></select></UiField>
          <UiField label="Rating"><select v-model="edit.rating" class="select" :disabled="!allowed('assurance:write')"><option :value="null">None</option><option v-for="r in ['satisfactory', 'needs_improvement', 'unsatisfactory']" :key="r" :value="r">{{ labelize(r) }}</option></select></UiField>
        </div>
      </template>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'assurance:read' })
useHead({ title: 'Assurance' })
const { can: allowed } = useAccess()
const { data, refresh } = await useFetch('/api/assurance')

const lineName = { 1: 'Management & control owners', 2: 'Compliance & risk', 3: 'Internal audit, external audit & regulators' } as Record<number, string>
const columns = [
  { key: 'name', label: 'Key control' },
  { key: 'l1', label: '1st line', width: '120px', sortValue: (r: any) => Number(r.lines[1]) },
  { key: 'l2', label: '2nd line', width: '120px', sortValue: (r: any) => Number(r.lines[2]) },
  { key: 'l3', label: '3rd line', width: '120px', sortValue: (r: any) => Number(r.lines[3]) },
  { key: 'status', label: 'Assessment', width: '150px' },
  { key: 'effectiveness', label: 'Tested effectiveness', width: '170px' },
]

const formOpen = ref(false)
type Activity = NonNullable<typeof data.value>['activities'][number]
const editing = ref<Activity | null>(null)
const edit = reactive({ status: 'planned', rating: null as string | null })
watch(editing, a => { if (a) Object.assign(edit, { status: a.status, rating: a.rating }) })
const act = useAction()
async function updateActivity() {
  if (!allowed('assurance:write')) { editing.value = null; return }
  if (await act.run(() => api.patch(`/api/assurance/activities/${editing.value!.id}`, edit), 'Activity updated')) { editing.value = null; refresh() }
}
</script>

<style scoped>
.lines { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
@media (max-width: 1100px) { .lines { grid-template-columns: 1fr; } }
.acts { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.act { display: flex; gap: 8px; align-items: center; width: 100%; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; text-align: left; cursor: pointer; }
.act:hover { border-color: var(--border-strong); }
</style>
