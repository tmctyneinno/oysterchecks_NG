<template>
  <div class="toasts" role="status" aria-live="polite">
    <TransitionGroup name="slide">
      <div v-for="t in toasts" :key="t.id" class="toast" :class="t.kind">
        <i :class="['bi', t.kind === 'error' ? 'bi-x-octagon-fill' : t.kind === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill']" aria-hidden="true" />
        <span style="flex:1">{{ t.message }}</span>
        <button class="icon-btn" style="width:22px;height:22px" type="button" aria-label="Dismiss" @click="dismiss(t.id)"><i class="bi bi-x" /></button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
const { toasts, dismiss } = useToast()
</script>
