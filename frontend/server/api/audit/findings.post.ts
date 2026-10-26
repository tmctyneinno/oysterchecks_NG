import { eq } from 'drizzle-orm'
import { z } from 'zod'

const body = z.object({
  engagementId: z.number().int(),
  title: z.string().trim().min(5).max(300),
  rating: z.enum(['high', 'medium', 'low']),
  ownerId: z.number().int(),
  dueAt: z.coerce.date(),
  raiseIncident: z.boolean().default(false),
})

export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'audit:write')
  const { raiseIncident, ...input } = await validateBody(event, body)
  const db = useDb()
  const t = tables
  const engagement = db.select().from(t.auditEngagements).where(eq(t.auditEngagements.id, input.engagementId)).get()
  if (!engagement) notFound('Engagement')
  const year = new Date().getFullYear()
  const row = db.insert(t.auditFindings).values({ ...input, businessUnit: engagement.businessUnit, status: 'open', ref: nextRef(t.auditFindings, t.auditFindings.ref, `F-${year}`, 3) }).returning().get()
  notifyUser(input.ownerId, { severity: input.rating === 'high' ? 'high' : 'info', title: `Audit finding ${row.ref} assigned`, body: row.title, link: '/audit' })
  // §4.8: audit findings feed Incident & Issue Management.
  if (raiseIncident) {
    db.insert(t.incidents).values({
      ref: nextRef(t.incidents, t.incidents.ref, 'INC', 4), title: row.title, type: 'audit_finding', severity: input.rating === 'high' ? 'high' : input.rating,
      status: 'open', businessUnit: engagement.businessUnit, reportedById: user.id, ownerId: input.ownerId, occurredAt: new Date(), reportedAt: new Date(),
      description: `Raised from audit finding ${row.ref} (${engagement.ref}).`,
    }).run()
  }
  writeAudit(actorOf(user), 'audit.finding_raised', 'audit_finding', row.id, { ref: row.ref, rating: row.rating }, event)
  return row
})
