<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="overlay" @click="close" />
    </Transition>
    <Transition name="slide">
      <aside v-if="open" ref="panel" class="drawer" role="dialog" aria-modal="true" :aria-label="title" tabindex="-1" @keydown.esc="close">
        <div class="drawer-head">
          <div style="min-width:0;flex:1">
            <div v-if="eyebrow" class="mono small muted">{{ eyebrow }}</div>
            <h2 style="font-size:18px;margin-top:2px">{{ title }}</h2>
            <div v-if="$slots.meta" class="row-wrap" style="margin-top:8px"><slot name="meta" /></div>
          </div>
          <button class="icon-btn" type="button" aria-label="Close" @click="close"><i class="bi bi-x-lg" /></button>
        </div>
        <div class="drawer-body">
          <div v-if="loading" class="empty"><span>Loading…</span></div>
          <slot v-else />
        </div>
        <div v-if="$slots.footer && !loading" class="drawer-foot"><slot name="footer" /></div>
      </aside>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps<{ open: boolean, title: string, eyebrow?: string, loading?: boolean }>()
const emit = defineEmits<{ close: [] }>()
const panel = ref<HTMLElement | null>(null)
const close = () => emit('close')

// Move focus into the drawer on open, back to the opener on close.
let opener: HTMLElement | null = null
watch(() => props.open, async (v) => {
  if (!import.meta.client) return
  if (v) {
    opener = document.activeElement as HTMLElement
    await nextTick()
    panel.value?.focus()
  }
  else opener?.focus?.()
})
</script>
