// Regulatory notification thresholds (§4.8 step 6). Whether an incident is notifiable is a human
// decision; once it is, the deadline is computed from when the firm became aware.
export const NOTIFICATION_RULES: Record<string, { regulator: string, hours: number, basis: string }> = {
  data: { regulator: 'ICO', hours: 72, basis: 'UK GDPR Art. 33: personal data breach within 72 hours of awareness' },
  operational: { regulator: 'FCA', hours: 24, basis: 'FCA PRIN 11 / SUP 15.3: material operational incident, prompt notification' },
  cyber: { regulator: 'FCA', hours: 24, basis: 'FCA SUP 15.3: material cyber incident, prompt notification' },
  regulatory: { regulator: 'FCA', hours: 72, basis: 'FCA SUP 15.3: significant breach of rules' },
  financial_crime: { regulator: 'NCA', hours: 168, basis: 'Handled through the SAR workflow; record decision here' },
  audit_finding: { regulator: 'FCA', hours: 72, basis: 'Only where the finding is itself a reportable breach' },
}
