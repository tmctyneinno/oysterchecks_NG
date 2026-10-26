<template>
  <header class="page-header">
    <div>
      <h1 class="page-title">{{ title }}</h1>
      <p v-if="subtitle || $slots.subtitle" class="page-sub">
        <slot name="subtitle">{{ subtitle }}</slot>
      </p>
    </div>
    <div class="spacer" />
    <div class="row-wrap no-print">
      <slot />
    </div>
  </header>
</template>

<script setup lang="ts">
defineProps<{ title: string, subtitle?: string }>()
</script>
