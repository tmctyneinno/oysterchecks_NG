<template>
  <div class="page">
    <UiPageHeader title="Users" :subtitle="`${users.length} accounts`">
      <button class="btn btn-primary" type="button" @click="createOpen = true"><i class="bi bi-person-plus" aria-hidden="true" /> Add user</button>
    </UiPageHeader>

    <UiPanel flush>
      <UiDataTable :columns="columns" :rows="users">
        <template #cell-name="{ row }"><div class="cell-title">{{ row.name }}</div><div class="cell-sub">{{ row.email }}</div></template>
        <template #cell-role="{ row }">
          <select class="select" style="width:auto" :value="row.role" :disabled="row.id === user?.id" :aria-label="`Role for ${row.name}`" @change="update(row.id, { role: ($event.target as HTMLSelectElement).value })">
            <option v-for="r in ROLE_KEYS" :key="r" :value="r">{{ ROLES[r].label }}</option>
          </select>
          <div v-if="row.businessUnit" class="cell-sub">{{ row.businessUnit }}</div>
        </template>
        <template #cell-security="{ row }">
          <div class="row-wrap" style="gap:6px">
            <UiPill :value="row.mfaEnabled ? 'ok' : 'high'" :text="row.mfaEnabled ? 'MFA on' : 'MFA not set up'" />
            <UiPill v-if="row.locked" value="critical" text="Locked" />
            <UiPill v-if="!row.active" tone="neutral" text="Disabled" />
          </div>
        </template>
        <template #cell-lastLoginAt="{ row }"><span class="small">{{ row.lastLoginAt ? fmtRelative(row.lastLoginAt) : 'Never' }}</span></template>
        <template #cell-actions="{ row }">
          <div v-if="row.id !== user?.id" class="row" style="gap:4px;justify-content:flex-end">
            <button v-if="row.locked" class="btn btn-sm" type="button" @click="update(row.id, { unlock: true })">Unlock</button>
            <button v-if="row.mfaEnabled" class="btn btn-sm" type="button" @click="update(row.id, { resetMfa: true })">Reset MFA</button>
            <button class="btn btn-sm" :class="row.active ? 'btn-danger' : ''" type="button" @click="update(row.id, { active: !row.active })">{{ row.active ? 'Disable' : 'Enable' }}</button>
          </div>
        </template>
      </UiDataTable>
    </UiPanel>

    <UiPanel title="Roles and permissions" flush>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Role</th><th>Landing</th><th>Scope</th><th>Restricted from</th><th>Permissions</th></tr></thead>
          <tbody>
            <tr v-for="r in Object.values(ROLES)" :key="r.key">
              <td class="cell-title">{{ r.label }}</td>
              <td class="mono small">{{ r.landing }}</td>
              <td class="small">{{ r.scope === 'bu' ? 'Own business unit' : 'All' }}</td>
              <td class="small text-2">{{ r.restrictedNote }}</td>
              <td class="small muted">{{ r.permissions.filter(p => !p.endsWith(':read')).join(', ') || 'Read only' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </UiPanel>

    <UiModal :open="createOpen" title="Add user" submit-label="Create account" :pending="act.pending.value" @close="createOpen = false" @submit="create">
      <div class="form-grid">
        <UiField label="Full name" required><input v-model="form.name" class="input"></UiField>
        <UiField label="Email" required><input v-model="form.email" type="email" class="input"></UiField>
        <UiField label="Role" required><select v-model="form.role" class="select"><option v-for="r in ROLE_KEYS" :key="r" :value="r">{{ ROLES[r].label }}</option></select></UiField>
        <UiField label="Business unit" :hint="ROLES[form.role].scope === 'bu' ? 'Required for this role' : 'Optional'"><select v-model="form.businessUnit" class="select"><option :value="null">None</option><option v-for="b in BUSINESS_UNITS" :key="b">{{ b }}</option></select></UiField>
      </div>
      <UiField label="Temporary password" hint="At least 12 characters. The user enrols MFA on first sign-in." required><input v-model="form.password" type="text" class="input mono" autocomplete="new-password"></UiField>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ permission: 'users:manage' })
useHead({ title: 'Users' })
const { user } = useAccess()
const { data, refresh } = await useFetch('/api/admin/users', { default: () => [] })
const users = computed(() => data.value)
const columns = [
  { key: 'name', label: 'User' },
  { key: 'role', label: 'Role', width: '260px' },
  { key: 'security', label: 'Security', width: '240px', sortable: false },
  { key: 'lastLoginAt', label: 'Last sign-in', width: '140px' },
  { key: 'actions', label: '', width: '260px', sortable: false },
]
const act = useAction()
async function update(id: number, body: object) {
  if (await act.run(() => api.patch(`/api/admin/users/${id}`, body), 'User updated. Role changes apply at next sign-in.')) refresh()
  else refresh()
}
const createOpen = ref(false)
const form = reactive({ name: '', email: '', role: 'COMPLIANCE_OFFICER' as RoleKey, businessUnit: null as string | null, password: '' })
async function create() {
  if (await act.run(() => api.post('/api/admin/users', form), 'User created')) { createOpen.value = false; refresh() }
}
</script>
