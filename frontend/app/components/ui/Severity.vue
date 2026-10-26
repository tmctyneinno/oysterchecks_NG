<template>
  <UiPill :value="value" :icon="SEVERITY_ICON[value]" :text="labelize(value)" />
</template>

<script setup lang="ts">
defineProps<{ value: string }>()
</script>
