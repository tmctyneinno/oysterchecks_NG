<template>
  <div class="shell">
    <a href="#main" class="skip-link">Skip to content</a>
    <LayoutAppHeader @toggle-nav="navOpen = !navOpen" />
    <LayoutAppSidebar :open="navOpen" @navigate="navOpen = false" />
    <div v-if="navOpen" class="nav-scrim" @click="navOpen = false" />
    <main id="main" class="app-main" tabindex="-1">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const navOpen = ref(false)
onMounted(connectLive)
onBeforeUnmount(disconnectLive)
</script>

<style scoped>
.shell { min-height: 100vh; }
.app-main { margin-left: var(--sidebar-w); padding-top: var(--header-h); min-height: 100vh; }
.app-main:focus { outline: none; }
.skip-link { position: absolute; left: -9999px; top: 8px; z-index: 100; background: var(--accent); color: var(--on-accent); padding: 8px 12px; border-radius: var(--r-md); font-weight: 500; }
.skip-link:focus { left: 8px; }
.nav-scrim { display: none; }
@media (max-width: 1024px) {
  .app-main { margin-left: 0; }
  .nav-scrim { display: block; position: fixed; inset: var(--header-h) 0 0 0; background: var(--overlay); z-index: 39; }
}
</style>
