import { z } from 'zod'
import { EVALUATOR_CATALOGUE } from '../ccm/catalogue'

export const slaSchema = z.discriminatedUnion('type', [
  z.object({ type: z.literal('minutes'), minutes: z.number().int().min(5).max(60 * 24 * 30) }),
  z.object({ type: z.literal('business_days'), days: z.number().int().min(1).max(30) }),
  z.object({ type: z.literal('end_of_day') }),
  z.object({ type: z.literal('immediate') }),
  z.object({ type: z.literal('before_entity_due'), minutes: z.number().int().min(0).max(60 * 24 * 30) }),
])

export const escalationSchema = z.array(z.enum(Object.keys(ESCALATION_TARGETS) as [EscalationTarget, ...EscalationTarget[]])).min(1).max(6)

/** Validates params against the evaluator's declared ranges; unknown params are rejected. */
export function validateParams(evaluator: string, params: Record<string, number>) {
  const spec = EVALUATOR_CATALOGUE[evaluator]
  if (!spec) throw createError({ statusCode: 400, statusMessage: `Unknown evaluator ${evaluator}` })
  for (const [k, v] of Object.entries(params)) {
    const p = spec.params[k]
    if (!p) throw createError({ statusCode: 400, statusMessage: `Unknown parameter ${k}` })
    if (typeof v !== 'number' || v < p.min || v > p.max) throw createError({ statusCode: 400, statusMessage: `${p.label} must be between ${p.min} and ${p.max}` })
  }
  return params
}
