import { eq } from 'drizzle-orm'
import { z } from 'zod'

const schemas = {
  prepare: z.object({}),
  submit: z.object({ reference: z.string().trim().min(3).max(80) }),
}

/** prepare = generate the return from platform data; submit = confirm submission to the regulator. */
export default defineEventHandler(async (event) => {
  const action = getRouterParam(event, 'action') as keyof typeof schemas
  if (!(action in schemas)) throw createError({ statusCode: 404, statusMessage: 'Unknown action' })
  const user = await requirePermission(event, action === 'submit' ? 'regreport:submit' : 'compliance:write')
  const id = parseId(event)
  const input = await validateBody(event, schemas[action]) as Record<string, any>
  const { regulatoryReports } = tables
  const db = useDb()
  const report = db.select().from(regulatoryReports).where(eq(regulatoryReports.id, id)).get()
  if (!report) notFound('Report')
  if (report.submittedAt) throw createError({ statusCode: 409, statusMessage: 'Already submitted' })

  const updated = action === 'prepare'
    ? db.update(regulatoryReports).set({ preparedAt: new Date() }).where(eq(regulatoryReports.id, id)).returning().get()
    : db.update(regulatoryReports).set({ submittedAt: new Date(), submittedById: user.id, reference: input.reference, preparedAt: report.preparedAt ?? new Date() }).where(eq(regulatoryReports.id, id)).returning().get()
  writeAudit(actorOf(user), `regulatory_report.${action === 'prepare' ? 'prepared' : 'submitted'}`, 'regulatory_report', id, { name: report.name, reference: input.reference }, event)
  if (action === 'submit') runCcmCycle()
  return updated
})
