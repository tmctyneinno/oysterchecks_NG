<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="overlay modal-overlay" @click="emit('close')" />
    </Transition>
    <Transition name="fade">
      <div v-if="open" ref="box" class="modal" role="dialog" aria-modal="true" :aria-label="title" tabindex="-1" @keydown.esc="emit('close')">
        <div class="modal-head">
          <h2 style="font-size:16px">{{ title }}</h2>
          <div class="spacer" />
          <button class="icon-btn" type="button" aria-label="Close" @click="emit('close')"><i class="bi bi-x-lg" /></button>
        </div>
        <form class="modal-body" novalidate @submit.prevent="emit('submit')">
          <slot />
          <div class="form-actions">
            <button type="button" class="btn btn-ghost" @click="emit('close')">Cancel</button>
            <button type="submit" class="btn" :class="danger ? 'btn-danger' : 'btn-primary'" :disabled="pending || disabled">
              <i v-if="pending" class="bi bi-arrow-repeat" aria-hidden="true" />
              {{ submitLabel ?? 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps<{ open: boolean, title: string, submitLabel?: string, pending?: boolean, disabled?: boolean, danger?: boolean }>()
const emit = defineEmits<{ close: [], submit: [] }>()
const box = ref<HTMLElement | null>(null)
watch(() => props.open, async (v) => {
  if (v && import.meta.client) {
    await nextTick()
    const first = box.value?.querySelector<HTMLElement>('input, select, textarea')
    ;(first ?? box.value)?.focus()
  }
})
</script>
