// Static demo build: there is no server. Every `/api/...` request is answered in the browser by
// app/demo/api.ts from hardcoded data, so `nuxt generate` output can be hosted anywhere.

import { loadFixtures } from '~/demo/fixtures'
import { download, handle, setChangeListener } from '~/demo/api'
import { OVERLAY_STORAGE_KEY, reloadOverlay } from '~/demo/state'

export default defineNuxtPlugin({
  name: 'demo-api',
  enforce: 'pre',
  async setup() {
    await loadFixtures()

    // Intercept at the fetch level: $fetch, useFetch and plain fetch all end up here, including the
    // $fetch references Nuxt compiles into components as direct imports.
    const nativeFetch = window.fetch.bind(window)
    // When hosted in a sub-folder, $fetch prefixes requests with it (/sub-folder/api/...).
    const base = useRuntimeConfig().app.baseURL.replace(/\/$/, '')
    window.fetch = async (input: RequestInfo | URL, init: RequestInit = {}) => {
      const url = new URL(input instanceof Request ? input.url : String(input), location.origin)
      const path = base && url.pathname.startsWith(`${base}/api/`) ? url.pathname.slice(base.length) : url.pathname
      if (url.origin !== location.origin || !path.startsWith('/api/')) return nativeFetch(input, init)

      const method = String(init.method ?? (input instanceof Request ? input.method : 'GET')).toUpperCase()
      let body: unknown = init.body
      if (typeof body === 'string') {
        try { body = JSON.parse(body) }
        catch { /* not JSON: pass through */ }
      }
      // A short pause so pending states and spinners behave as they would against a real API.
      if (method !== 'GET') await new Promise(r => setTimeout(r, 150 + Math.random() * 150))
      try {
        const result = await handle(method, path,Object.fromEntries(url.searchParams), body)
        return new Response(JSON.stringify(result), { status: 200, headers: { 'content-type': 'application/json' } })
      }
      catch (err) {
        const e = err as { statusCode?: number, statusMessage?: string, message?: string }
        const statusCode = e.statusCode ?? 500
        const statusMessage = e.statusMessage ?? e.message ?? 'Demo API error'
        if (!e.statusCode) console.error(err)
        // Header-safe status text; the full message travels in the JSON body as Nitro would send it.
        return new Response(JSON.stringify({ statusCode, statusMessage, message: statusMessage }), {
          status: statusCode, statusText: statusMessage.replace(/[^\x20-\x7E]/g, ' ').slice(0, 200), headers: { 'content-type': 'application/json' },
        })
      }
    }

    // Changes made here refresh the screens in this tab; changes made in another tab arrive
    // through the storage event and refresh the screens here too.
    setChangeListener(types => types.forEach(t => dispatchLive(t, { source: 'local' })))
    window.addEventListener('storage', (e) => {
      if (e.key !== OVERLAY_STORAGE_KEY) return
      new Set([...reloadOverlay(), 'data']).forEach(t => dispatchLive(t, { source: 'remote' }))
    })

    // Export and evidence links point at `/api/...` URLs; serve them as browser downloads.
    document.addEventListener('click', (e) => {
      const link = (e.target as HTMLElement | null)?.closest?.('a[href^="/api/"]') as HTMLAnchorElement | null
      if (!link || e.defaultPrevented || e.button !== 0) return
      e.preventDefault()
      try {
        const { filename, blob } = download(link.getAttribute('href')!)
        const href = URL.createObjectURL(blob)
        const a = Object.assign(document.createElement('a'), { href, download: filename })
        document.body.appendChild(a)
        a.click()
        a.remove()
        setTimeout(() => URL.revokeObjectURL(href), 10_000)
      }
      catch (err) {
        useToast().error(errorMessage(err))
      }
    })
  },
})
