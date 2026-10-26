<template>
  <form class="stack" novalidate @submit.prevent="submit">
    <div v-if="error" class="callout danger" role="alert"><i class="bi bi-x-octagon" aria-hidden="true" /><span>{{ error }}</span></div>
    <UiField label="Verification code" required>
      <input
        v-model="code"
        class="input mono"
        inputmode="numeric"
        autocomplete="one-time-code"
        pattern="[0-9]{6}"
        maxlength="6"
        placeholder="000000"
        style="font-size:22px;letter-spacing:0.4em;text-align:center"
        autofocus
        @input="code = code.replace(/\D/g, '')"
      >
    </UiField>
    <button class="btn btn-primary" type="submit" :disabled="pending || code.length !== 6" style="width:100%;min-height:40px">
      {{ pending ? 'Verifying' : 'Verify' }}
    </button>
    <NuxtLink to="/login" class="small" style="text-align:center">Start again</NuxtLink>
  </form>
</template>

<script setup lang="ts">
const route = useRoute()
const code = ref('')
const pending = ref(false)
const error = ref<string | null>(null)
const { fetch: refreshSession } = useUserSession()

async function submit() {
  pending.value = true
  error.value = null
  try {
    const res = await $fetch<{ next: string }>('/api/auth/mfa/verify', { method: 'POST', body: { code: code.value } })
    await refreshSession()
    await navigateTo(typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') ? route.query.redirect : res.next)
  }
  catch (e) {
    error.value = errorMessage(e)
    code.value = ''
  }
  finally { pending.value = false }
}
</script>
