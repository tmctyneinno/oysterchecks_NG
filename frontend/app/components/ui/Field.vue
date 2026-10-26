<template>
  <label class="field">
    <span class="field-label">{{ label }}<span v-if="required" aria-hidden="true"> *</span></span>
    <slot />
    <span v-if="hint" class="field-hint">{{ hint }}</span>
  </label>
</template>

<script setup lang="ts">
defineProps<{ label: string, hint?: string, required?: boolean }>()
</script>
