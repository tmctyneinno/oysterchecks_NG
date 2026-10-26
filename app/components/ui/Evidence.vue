<template>
  <div class="stack" style="gap:8px">
    <div class="row">
      <div class="section-title" style="margin:0">Evidence</div>
      <div class="spacer" />
      <label v-if="canUpload" class="btn btn-sm">
        <i class="bi bi-paperclip" aria-hidden="true" /> {{ uploading ? 'Uploading…' : 'Attach file' }}
        <input type="file" class="sr-only" :disabled="uploading" accept=".pdf,.png,.jpg,.jpeg,.txt,.csv,.docx,.xlsx,.eml" @change="upload">
      </label>
    </div>
    <ul v-if="items.length" class="stack" style="gap:6px;list-style:none;padding:0;margin:0">
      <li v-for="e in items" :key="e.id" class="row small" style="gap:10px">
        <i class="bi bi-file-earmark-text muted" aria-hidden="true" />
        <a :href="`/api/evidence/${e.id}`" class="nowrap" style="overflow:hidden;text-overflow:ellipsis;max-width:240px">{{ e.filename }}</a>
        <span class="muted">{{ e.uploadedByName }} · {{ fmtDateTime(e.uploadedAt) }}</span>
        <span class="spacer" />
        <code class="muted" :title="`SHA-256 ${e.sha256}`">{{ e.sha256.slice(0, 10) }}…</code>
      </li>
    </ul>
    <p v-else class="small muted">No files attached.</p>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{ entityType: string, entityId: number, canUpload?: boolean }>()
const { data, refresh } = useFetch('/api/evidence', { query: computed(() => ({ entityType: props.entityType, entityId: props.entityId })), default: () => [] })
const items = computed(() => data.value)
const uploading = ref(false)
const toast = useToast()

async function upload(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  const form = new FormData()
  form.append('entityType', props.entityType)
  form.append('entityId', String(props.entityId))
  form.append('file', file)
  uploading.value = true
  try {
    await $fetch('/api/evidence', { method: 'POST', body: form })
    toast.success(`${file.name} attached`)
    await refresh()
  }
  catch (err) { toast.error(errorMessage(err)) }
  finally { uploading.value = false; input.value = '' }
}
</script>
