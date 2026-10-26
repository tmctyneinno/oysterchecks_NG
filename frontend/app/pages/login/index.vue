<template>
  <form class="stack-lg" novalidate @submit.prevent="submit">
    <div>
      <h1 style="font-size:20px">Sign in</h1>
      <p class="small muted" style="margin-top:4px">Use your work email and password.</p>
    </div>

    <div v-if="error" class="callout danger" role="alert"><i class="bi bi-x-octagon" aria-hidden="true" /><span>{{ error }}</span></div>

    <UiField label="Email" required>
      <input v-model.trim="email" class="input" type="email" autocomplete="username" required autofocus>
    </UiField>
    <UiField label="Password" required>
      <div style="position:relative">
        <input v-model="password" class="input" :type="show ? 'text' : 'password'" autocomplete="current-password" required style="padding-right:40px">
        <button type="button" class="icon-btn" style="position:absolute;right:0;top:0;height:34px" :aria-label="show ? 'Hide password' : 'Show password'" @click="show = !show">
          <i :class="['bi', show ? 'bi-eye-slash' : 'bi-eye']" />
        </button>
      </div>
    </UiField>

    <button class="btn btn-primary" type="submit" :disabled="pending || !email || !password" style="width:100%;min-height:40px">
      {{ pending ? 'Signing in' : 'Sign in' }}
    </button>

    <details class="demo">
      <summary class="small">Demo accounts</summary>
      <p class="small muted" style="margin:8px 0">All demo accounts use the password <code>OysterDemo!2026</code>.</p>
      <div class="demo-grid">
        <button v-for="d in demo" :key="d.email" type="button" class="btn btn-sm btn-ghost" style="justify-content:flex-start" @click="email = d.email; password = 'OysterDemo!2026'">
          <span class="small">{{ d.label }}</span>
        </button>
      </div>
    </details>
  </form>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'auth', public: true })
useHead({ title: 'Sign in' })

const route = useRoute()
const email = ref('')
const password = ref('')
const show = ref(false)
const pending = ref(false)
const error = ref<string | null>(null)
const { fetch: refreshSession } = useUserSession()

const demo = [
  { email: 'cro@oysterchecks.demo', label: 'CRO' }, { email: 'cco@oysterchecks.demo', label: 'CCO' },
  { email: 'mlro@oysterchecks.demo', label: 'MLRO' }, { email: 'analyst@oysterchecks.demo', label: 'AML Analyst' },
  { email: 'compliance@oysterchecks.demo', label: 'Compliance' }, { email: 'audit@oysterchecks.demo', label: 'Head of Audit' },
  { email: 'ciso@oysterchecks.demo', label: 'CISO' }, { email: 'hr@oysterchecks.demo', label: 'HR Director' },
  { email: 'retail.manager@oysterchecks.demo', label: 'BU Manager' }, { email: 'board@oysterchecks.demo', label: 'Board' },
  { email: 'admin@oysterchecks.demo', label: 'Admin' },
]

async function submit() {
  pending.value = true
  error.value = null
  try {
    const res = await $fetch<{ next: string }>('/api/auth/login', { method: 'POST', body: { email: email.value, password: password.value } })
    if (res.next.startsWith('/login/')) return navigateTo({ path: res.next, query: route.query })
    await refreshSession()
    await navigateTo(typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') ? route.query.redirect : res.next)
  }
  catch (e) { error.value = errorMessage(e) }
  finally { pending.value = false }
}
</script>

<style scoped>
.demo { border-top: 1px solid var(--border); padding-top: 12px; }
.demo summary { cursor: pointer; color: var(--text-2); }
.demo-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 4px; }
</style>
