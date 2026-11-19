// Routes added for the Version 1.2 capabilities (app/demo/api-v12.ts). Their records are plain demo
// JSON, so they are typed loosely; the fixture-backed routes are typed in demo-api.d.ts.

declare module 'nitropack/types' {
  interface InternalApi {
    '/api/perimeter/overview': { get: any }
    '/api/perimeter/entities': { get: any[] }
    '/api/perimeter/sources': { get: any[] }
    '/api/perimeter/comparisons': { get: any[] }
    '/api/perimeter/changes': { get: any[] }
    '/api/perimeter/changes/:id': { get: any }
    '/api/trace/obligation/:id': { get: any }
    '/api/tm/overview': { get: any }
    '/api/tm/alerts/:id': { get: any }
    '/api/crypto/overview': { get: any }
    '/api/programme/gates': { get: any[] }
  }
}

export {}
