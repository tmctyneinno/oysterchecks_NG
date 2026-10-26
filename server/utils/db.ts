import { mkdirSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import Database from 'better-sqlite3'
import { drizzle, type BetterSQLite3Database } from 'drizzle-orm/better-sqlite3'
import * as schema from '../database/schema'

export const tables = schema
export type Db = BetterSQLite3Database<typeof schema>

let instance: { sqlite: Database.Database, db: Db } | null = null

export function openDatabase(path: string) {
  if (path !== ':memory:') mkdirSync(dirname(resolve(path)), { recursive: true })
  const sqlite = new Database(path)
  sqlite.pragma('journal_mode = WAL')
  sqlite.pragma('foreign_keys = ON')
  return { sqlite, db: drizzle(sqlite, { schema }) }
}

function connection() {
  if (!instance) instance = openDatabase(useRuntimeConfig().dbPath)
  return instance
}

export const useDb = (): Db => connection().db
export const useSqlite = () => connection().sqlite

// Migrations run asynchronously at boot; requests wait on this (see server/middleware/00.ready.ts).
let ready: Promise<void> = Promise.resolve()
export const setDbReady = (p: Promise<void>) => { ready = p }
export const whenDbReady = () => ready
