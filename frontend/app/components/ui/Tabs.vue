<template>
  <div class="tabs" role="tablist">
    <button
      v-for="t in tabs"
      :key="t.key"
      type="button"
      role="tab"
      class="tab"
      :class="{ active: modelValue === t.key }"
      :aria-selected="modelValue === t.key"
      @click="emit('update:modelValue', t.key)"
    >
      {{ t.label }}
      <span v-if="t.count !== undefined" class="tab-count">{{ t.count }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
defineProps<{ modelValue: string, tabs: { key: string, label: string, count?: number }[] }>()
const emit = defineEmits<{ 'update:modelValue': [string] }>()
</script>
