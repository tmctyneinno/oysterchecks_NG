import type { H3Event } from 'h3'
import type { z } from 'zod'

const humanise = (path: PropertyKey[]) => {
  const key = String(path.at(-1) ?? '')
  return key ? key.replace(/([A-Z])/g, ' $1').replace(/^./, c => c.toUpperCase()) : ''
}

/**
 * Parses the request body with a Zod schema. On failure responds 400 with the first issue as a
 * readable statusMessage (e.g. "Title: Too small…") and the full issue list in `data.issues`.
 */
export async function validateBody<T extends z.ZodType>(event: H3Event, schema: T): Promise<z.output<T>> {
  const body = await readBody(event).catch(() => undefined)
  const result = schema.safeParse(body ?? {})
  if (!result.success) {
    const first = result.error.issues[0]!
    const field = humanise(first.path)
    throw createError({ statusCode: 400, statusMessage: field ? `${field}: ${first.message}` : first.message, data: { issues: result.error.issues } })
  }
  return result.data
}
