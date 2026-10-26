// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['nuxt-auth-utils'],

  app: {
    head: {
      htmlAttrs: { lang: 'en-GB' },
      title: 'OysterChecks',
      meta: [
        { name: 'description', content: 'OysterChecks governance, risk, compliance and assurance platform.' },
        { name: 'theme-color', content: '#F5F6F8', media: '(prefers-color-scheme: light)' },
        { name: 'theme-color', content: '#0F1113', media: '(prefers-color-scheme: dark)' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap' },
      ],
    },
  },

  css: ['bootstrap-icons/font/bootstrap-icons.css', '~/assets/css/main.css'],

  runtimeConfig: {
    // Overridable via NUXT_DB_PATH etc. See .env.example.
    dbPath: '.data/oysterchecks.db',
    evidenceDir: '.data/evidence',
    mfaEnforced: true,
    seedPassword: 'OysterDemo!2026',
    demoSimulator: true,
    ccmIntervalSeconds: 60,
    session: {
      maxAge: 60 * 60 * 8,
    },
  },

  nitro: {
    serverAssets: [{ baseName: 'migrations', dir: './database/migrations' }],
  },

  typescript: { strict: true },
})
