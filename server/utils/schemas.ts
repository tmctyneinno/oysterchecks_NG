import { z } from 'zod'

// Request schemas shared between create (POST) and update (PATCH) handlers.

export const riskBody = z.object({
  title: z.string().trim().min(3).max(200),
  description: z.string().trim().max(2000).nullish(),
  category: z.string().trim().min(2).max(60),
  ownerId: z.number().int(),
  businessUnit: z.string().nullish(),
  impact: z.number().int().min(1).max(5),
  likelihood: z.number().int().min(1).max(5),
  treatment: z.enum(['accept', 'mitigate', 'transfer', 'avoid']),
  treatmentPlan: z.string().trim().max(2000).nullish(),
  treatmentDueAt: z.coerce.date().nullish(),
  // No .default(): in Zod 4 defaults also fire inside .partial(), which would wipe links on PATCH.
  controlIds: z.array(z.number().int()).optional(),
})

export const controlBody = z.object({
  name: z.string().trim().min(3).max(200),
  description: z.string().trim().max(2000).nullish(),
  ownerId: z.number().int(),
  businessUnit: z.string().min(2),
  type: z.enum(['preventive', 'detective', 'corrective']),
  frequency: z.enum(['continuous', 'daily', 'weekly', 'monthly', 'quarterly', 'annual']),
  automated: z.boolean(),
  keyControl: z.boolean(),
  ccmRuleId: z.string().nullish(),
  riskIds: z.array(z.number().int()).optional(),
})

export const obligationBody = z.object({
  title: z.string().trim().min(3).max(300),
  sourceType: z.enum(['law', 'regulation', 'standard', 'internal_policy']),
  framework: z.string().trim().min(2).max(80),
  regulator: z.string().trim().max(80).nullish(),
  jurisdiction: z.string().trim().min(2).max(40),
  businessUnit: z.string().nullish(),
  ownerId: z.number().int(),
  status: z.enum(['compliant', 'partial', 'non_compliant', 'not_assessed']),
  nextReviewAt: z.coerce.date().nullish(),
  controlIds: z.array(z.number().int()).optional(),
})

export const policyBody = z.object({
  title: z.string().trim().min(3).max(200),
  summary: z.string().trim().max(4000).nullish(),
  ownerId: z.number().int(),
  jurisdiction: z.string().trim().min(2).max(40),
  frameworks: z.array(z.string().trim().min(1)).max(10),
  reviewDate: z.coerce.date(),
  riskIds: z.array(z.number().int()).optional(),
  controlIds: z.array(z.number().int()).optional(),
  obligationIds: z.array(z.number().int()).optional(),
})

export const supplierBody = z.object({
  name: z.string().trim().min(2).max(200),
  services: z.string().trim().min(2).max(300),
  ownerId: z.number().int(),
  country: z.string().trim().length(2),
  dataAccess: z.boolean(),
  annualSpend: z.number().min(0),
  contractRenewalAt: z.coerce.date().nullish(),
  dpaInPlace: z.boolean(),
})

export const incidentBody = z.object({
  title: z.string().trim().min(3).max(200),
  description: z.string().trim().max(4000).nullish(),
  type: z.enum(['operational', 'data', 'financial_crime', 'regulatory', 'audit_finding', 'cyber']),
  severity: z.enum(['critical', 'high', 'medium', 'low']),
  businessUnit: z.string().min(2),
  occurredAt: z.coerce.date(),
  ownerId: z.number().int().nullish(),
})
