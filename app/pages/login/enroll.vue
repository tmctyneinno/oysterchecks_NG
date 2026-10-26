<template>
  <div class="stack-lg">
    <div>
      <h2 style="font-size:20px">Set up two-step verification</h2>
      <p class="small muted" style="margin-top:4px">Scan the QR code with an authenticator app such as Microsoft Authenticator or Google Authenticator, then enter the code it shows.</p>
    </div>
    <div v-if="error" class="callout danger" role="alert"><i class="bi bi-x-octagon" aria-hidden="true" /><span>{{ error }}</span> <NuxtLink to="/login">Start again</NuxtLink></div>
    <div v-else-if="data" class="stack" style="align-items:center">
      <img :src="data.qr" width="200" height="200" alt="QR code for authenticator app enrolment" style="border-radius:8px">
      <div class="small muted">Or enter this key manually:</div>
      <code class="mono" style="font-size:13px;letter-spacing:0.08em">{{ data.secret }}</code>
    </div>
    <AuthCodeForm v-if="data" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'auth', public: true })
useHead({ title: 'Set up MFA' })
const { data, error: fetchError } = await useFetch('/api/auth/mfa/enroll')
const error = computed(() => (fetchError.value ? errorMessage(fetchError.value) : null))
</script>
