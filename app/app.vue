<template>
  <NuxtRouteAnnouncer />
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
  <UiToasts />
</template>

<script setup lang="ts">
const { choice } = useTheme()
useHead({
  titleTemplate: t => (t ? `${t} | OysterChecks` : 'OysterChecks'),
  htmlAttrs: { 'data-theme': computed(() => (choice.value === 'system' ? undefined : choice.value)) },
})
</script>
