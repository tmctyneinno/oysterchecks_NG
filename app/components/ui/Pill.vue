<template>
  <span class="pill" :class="`tone-${resolvedTone}`">
    <i v-if="icon" :class="['bi', icon]" aria-hidden="true" />
    <slot>{{ text ?? labelize(value) }}</slot>
  </span>
</template>

<script setup lang="ts">
// Status is never shown by colour alone: tags always carry a text label.
const props = defineProps<{ value?: string | null, text?: string, tone?: Tone, icon?: string }>()
const resolvedTone = computed(() => props.tone ?? toneFor(props.value))
</script>
