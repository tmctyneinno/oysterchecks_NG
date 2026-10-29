// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  // Static demo: the whole app runs in the browser and answers its own /api calls from hardcoded
  // data (app/demo). `npm run generate` produces plain files that any static host can serve.
  ssr: false,

  app: {
    // Compiled scripts, styles and fonts are published here (Nuxt's default is /_nuxt/).
    buildAssetsDir: '/_assets/',
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
      // Applies the saved theme before the app boots, so there is no light flash in dark mode.
      script: [{ innerHTML: `(function(){var m=document.cookie.match(/(?:^|; )oc-theme=(light|dark)/);if(m)document.documentElement.setAttribute('data-theme',m[1])})()` }],
    },
  },

  css: ['bootstrap-icons/font/bootstrap-icons.css', '~/assets/css/main.css'],

  typescript: { strict: true },
})
