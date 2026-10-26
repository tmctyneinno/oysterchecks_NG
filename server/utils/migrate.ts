import type Database from 'better-sqlite3'

interface Journal { entries: { idx: number, tag: string }[] }

/**
 * Applies drizzle-kit generated migrations. SQL files are read through Nitro server assets so they
 * are bundled into the production build (no dependency on the source tree at runtime).
 */
export async function runMigrations(sqlite: Database.Database, read: (key: string) => Promise<string | null>) {
  sqlite.exec('CREATE TABLE IF NOT EXISTS __migrations (tag TEXT PRIMARY KEY, applied_at INTEGER NOT NULL)')
  const applied = new Set((sqlite.prepare('SELECT tag FROM __migrations').all() as { tag: string }[]).map(r => r.tag))

  const journalRaw = await read('meta/_journal.json')
  if (!journalRaw) throw new Error('Migration journal not found')
  const journal: Journal = typeof journalRaw === 'string' ? JSON.parse(journalRaw) : journalRaw

  for (const entry of journal.entries.sort((a, b) => a.idx - b.idx)) {
    if (applied.has(entry.tag)) continue
    const sqlText = await read(`${entry.tag}.sql`)
    if (!sqlText) throw new Error(`Migration ${entry.tag} missing`)
    const statements = String(sqlText).split('--> statement-breakpoint').map(s => s.trim()).filter(Boolean)
    sqlite.transaction(() => {
      for (const stmt of statements) sqlite.exec(stmt)
      sqlite.prepare('INSERT INTO __migrations (tag, applied_at) VALUES (?, ?)').run(entry.tag, Date.now())
    })()
  }

  // Immutability guard for the audit log (§2.3). Kept outside drizzle-kit because it is not schema.
  sqlite.exec(`
    CREATE TRIGGER IF NOT EXISTS audit_log_no_update BEFORE UPDATE ON audit_log
      BEGIN SELECT RAISE(ABORT, 'audit_log is append-only'); END;
    CREATE TRIGGER IF NOT EXISTS audit_log_no_delete BEFORE DELETE ON audit_log
      BEGIN SELECT RAISE(ABORT, 'audit_log is append-only'); END;
  `)
}
