import { sql } from 'drizzle-orm'
import { DEMO_USERS, seedDatabase } from '../database/seed'

const g = globalThis as unknown as { __ocTimer?: ReturnType<typeof setInterval> }

/** Boot sequence: migrate → seed (first run only) → first CCM cycle → schedule recurring cycles. */
export default defineNitroPlugin(() => {
  const config = useRuntimeConfig()

  setDbReady((async () => {
    const storage = useStorage('assets:migrations')
    await runMigrations(useSqlite(), async key => (await storage.getItem<string>(key.replace(/\//g, ':'))) as string | null)

    const db = useDb()
    const count = db.select({ n: sql<number>`count(*)` }).from(tables.users).get()?.n ?? 0
    if (count === 0) {
      console.info('[platform] Empty database: seeding demo dataset')
      seedDatabase(db, await hashPassword(config.seedPassword))
      writeAudit({ id: null, name: 'System' }, 'platform.seeded', 'platform', null, { users: DEMO_USERS.length })
    }
  })())

  whenDbReady().then(() => {
    const tick = () => {
      try {
        if (config.demoSimulator) simulateTick()
        runCcmCycle()
      }
      catch (err) {
        console.error('[platform] CCM cycle failed', err)
      }
    }
    tick()
    if (g.__ocTimer) clearInterval(g.__ocTimer)
    g.__ocTimer = setInterval(tick, Math.max(10, Number(config.ccmIntervalSeconds)) * 1000)
  }).catch(err => console.error('[platform] Database initialisation failed', err))
})
