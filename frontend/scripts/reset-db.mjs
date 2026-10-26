// Deletes the local SQLite database so the next `npm run dev` re-migrates and re-seeds.
import { existsSync, rmSync } from 'node:fs'

const path = process.env.NUXT_DB_PATH || '.data/oysterchecks.db'
for (const f of [path, `${path}-wal`, `${path}-shm`]) {
  if (existsSync(f)) rmSync(f)
}
console.log(`Removed ${path}. Restart the dev server to re-seed.`)
