<template>
  <div class="stack-lg">
    <div>
      <h2 style="font-size:20px">Two-step verification</h2>
      <p class="small muted" style="margin-top:4px">Enter the 6-digit code from your authenticator app.</p>
    </div>
    <AuthCodeForm />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'auth', public: true })
useHead({ title: 'Verify' })
</script>
