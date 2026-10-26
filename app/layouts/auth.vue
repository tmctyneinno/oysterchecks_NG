<template>
  <div class="auth">
    <header class="top">
      <span class="brand"><span class="mark" aria-hidden="true">OC</span>OysterChecks</span>
      <button class="icon-btn" type="button" :aria-label="`${themeLabel}. Click to change`" :title="themeLabel" @click="cycle"><i :class="['bi', themeIcon]" /></button>
    </header>
    <main class="center">
      <div class="card">
        <slot />
      </div>
      <p class="small muted note">Access to this system is logged.</p>
    </main>
  </div>
</template>

<script setup lang="ts">
const { cycle, label: themeLabel, icon: themeIcon } = useTheme()
</script>

<style scoped>
.auth { min-height: 100vh; display: flex; flex-direction: column; background: var(--bg); }
.top { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; }
.brand { display: inline-flex; align-items: center; gap: 9px; font-weight: 600; font-size: 15px; }
.mark { width: 28px; height: 28px; border-radius: var(--r-md); background: var(--accent); color: var(--on-accent); font-weight: 600; display: grid; place-items: center; font-size: 12px; }
.center { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 6vh 16px 32px; }
.card { width: 100%; max-width: 400px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 28px; }
.note { margin-top: 16px; }
</style>
