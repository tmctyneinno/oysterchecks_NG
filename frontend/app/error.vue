<template>
  <div class="wrap">
    <div class="box">
      <div class="mono muted">Error {{ error?.statusCode }}</div>
      <h1 class="page-title">{{ error?.statusCode === 404 ? 'Page not found' : 'Something went wrong' }}</h1>
      <p class="text-2">{{ error?.statusCode === 404 ? 'That screen does not exist or has moved.' : error?.statusMessage || 'An unexpected error occurred. The issue has been logged.' }}</p>
      <button class="btn btn-primary" type="button" @click="clearError({ redirect: '/' })">Back to safety</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { NuxtError } from '#app'
defineProps<{ error: NuxtError }>()
</script>

<style scoped>
.wrap { min-height: 100vh; display: grid; place-items: center; padding: 16px; }
.box { max-width: 460px; display: flex; flex-direction: column; gap: 12px; }
</style>
