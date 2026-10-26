import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  value: z.number().nullable(),
  evidenceNote: z.string().trim().min(5, 'Cite the evidence source for this figure').max(2000),
})

/** Status is derived from value vs target (±10% band = at risk), not chosen by hand. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'esg:write')
  const id = parseId(event)
  const input = await validateBody(event, body)
  const { esgMetrics } = tables
  const m = useDb().select().from(esgMetrics).where(eq(esgMetrics.id, id)).get()
  if (!m) notFound('Metric')

  let status: typeof m.status = 'no_data'
  if (input.value !== null && m.target !== null) {
    const better = m.lowerIsBetter ? input.value <= m.target : input.value >= m.target
    const gap = Math.abs(input.value - m.target) / Math.max(Math.abs(m.target), 1)
    status = better ? 'on_track' : gap <= 0.1 ? 'at_risk' : 'off_track'
  }
  else if (input.value !== null) status = 'on_track'

  const updated = useDb().update(esgMetrics).set({ ...input, status }).where(eq(esgMetrics.id, id)).returning().get()
  writeAudit(actorOf(user), 'esg.metric_updated', 'esg_metric', id, { name: m.name, from: m.value, to: input.value, status }, event)
  return updated
})
