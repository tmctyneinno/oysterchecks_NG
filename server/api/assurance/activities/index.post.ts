import { z } from 'zod'

const body = z.object({
  line: z.union([z.literal(1), z.literal(2), z.literal(3)]),
  provider: z.enum(['management', 'compliance', 'risk', 'internal_audit', 'external_audit', 'regulator']),
  title: z.string().trim().min(3).max(200),
  activityType: z.enum(['csa', 'monitoring', 'testing', 'audit', 'review']),
  plannedStart: z.coerce.date(),
  plannedEnd: z.coerce.date(),
  ownerId: z.number().int(),
  controlIds: z.array(z.number().int()).max(50).optional(),
}).refine(b => b.plannedEnd >= b.plannedStart, 'End must be after start')

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'assurance:write')
  const { controlIds, ...input } = await validateBody(event, body)
  const row = useDb().insert(tables.assuranceActivities).values({ ...input, status: 'planned' }).returning().get()
  setLinks('assurance', row.id, 'control', controlIds ?? [])
  writeAudit(actorOf(user), 'assurance.planned', 'assurance_activity', row.id, { title: row.title, line: row.line }, event)
  return row
})
