<template>
  <section class="panel" :aria-label="title">
    <div v-if="title || $slots.actions" class="panel-head">
      <div>
        <div class="panel-title">{{ title }}</div>
        <div v-if="sub" class="panel-sub">{{ sub }}</div>
      </div>
      <div class="spacer" />
      <slot name="actions" />
    </div>
    <div class="panel-body" :class="{ flush }">
      <slot />
    </div>
  </section>
</template>

<script setup lang="ts">
defineProps<{ title?: string, sub?: string, flush?: boolean }>()
</script>
