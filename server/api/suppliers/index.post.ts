import { z } from 'zod'

const body = supplierBody.extend({ regulatoryCritical: z.boolean() })

/** Vendor onboarding (§4.7 steps 1–3): register, run automated checks, classify. */
export default defineEventHandler(async (event) => {
  const user = await requirePermission(event, 'tprm:write')
  const { regulatoryCritical, ...input } = await validateBody(event, body)
  const tier = classifySupplier({ ...input, countryRisk: countryRiskOf(input.country), questionnaireScore: null, regulatoryCritical })
  const { suppliers } = tables
  const row = useDb().insert(suppliers).values({
    ...input, ref: nextRef(suppliers, suppliers.ref, 'SUP', 3), tier, ddStatus: 'in_progress',
    // Sanctions/adverse-media screening is requested from the LSEG connector on creation.
    sanctionsStatus: 'pending', monitoringFlags: [], status: 'onboarding',
  }).returning().get()
  writeAudit(actorOf(user), 'supplier.onboarded', 'supplier', row.id, { ref: row.ref, tier }, event)
  return row
})
