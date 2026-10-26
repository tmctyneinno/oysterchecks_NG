<template>
  <select v-model="model" class="select" :required="required">
    <option :value="null" disabled>Select a person</option>
    <option v-for="u in options" :key="u.id" :value="u.id">{{ u.name }} ({{ u.roleLabel }})</option>
  </select>
</template>

<script setup lang="ts">
const props = defineProps<{ roles?: RoleKey[], required?: boolean, excludeId?: number }>()
const model = defineModel<number | null>({ default: null })
const { users } = useDirectory()
const options = computed(() => users.value.filter(u => (!props.roles || props.roles.includes(u.role)) && u.id !== props.excludeId))
</script>
