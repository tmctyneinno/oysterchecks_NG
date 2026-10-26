// Evaluator catalogue: what each evaluator watches and which params a rule may tune.
// Used to validate rule edits and to render the "New rule" form (rules reuse evaluators with new params).

export const EVALUATOR_CATALOGUE: Record<string, { label: string, params: Record<string, { label: string, min: number, max: number }> }> = {
  unverified_onboarding: { label: 'Unverified onboarding (KYC age)', params: { hours: { label: 'Hours without verified KYC', min: 1, max: 720 } } },
  unscreened_high_value: { label: 'Unscreened high-value transaction', params: { threshold: { label: 'Threshold (£)', min: 100, max: 10_000_000 }, graceMinutes: { label: 'Screening grace (minutes)', min: 0, max: 240 } } },
  sar_overdue: { label: 'SAR not submitted after decision', params: { days: { label: 'Days after decision', min: 1, max: 30 }, warnDays: { label: 'Warn after (days)', min: 0, max: 30 } } },
  edd_incomplete: { label: 'EDD incomplete for high-risk customer', params: { days: { label: 'Days allowed', min: 1, max: 90 } } },
  kyc_expired: { label: 'KYC refresh overdue (risk-based)', params: { standardMonths: { label: 'Standard (months)', min: 1, max: 60 }, enhancedMonths: { label: 'Enhanced (months)', min: 1, max: 36 }, highMonths: { label: 'High (months)', min: 1, max: 24 }, warnDays: { label: 'Warn window (days)', min: 0, max: 90 } } },
  new_starter_vetting: { label: 'New starter without vetting clearance', params: { days: { label: 'Days before start', min: 0, max: 30 }, warnDays: { label: 'Warn window (days)', min: 0, max: 60 } } },
  privileged_access_review: { label: 'Privileged access review age', params: { days: { label: 'Max days since review', min: 7, max: 365 }, warnDays: { label: 'Warn window (days)', min: 0, max: 60 } } },
  reg_report_deadline: { label: 'Regulatory report deadline', params: { days: { label: 'Days before due', min: 0, max: 30 }, warnDays: { label: 'Warn window (days)', min: 0, max: 60 } } },
  csa_overdue: { label: 'Control self-assessment overdue', params: { warnDays: { label: 'Warn window (days)', min: 0, max: 30 } } },
  audit_rec_overdue: { label: 'Audit recommendation overdue', params: { warnDays: { label: 'Warn window (days)', min: 0, max: 60 } } },
  anomalous_volume: { label: 'Anomalous transaction volume', params: { sigma: { label: 'Standard deviations', min: 1, max: 10 }, baselineDays: { label: 'Baseline (days)', min: 14, max: 365 }, minBaselineDays: { label: 'Min active baseline days', min: 1, max: 90 } } },
  control_double_fail: { label: 'Consecutive control test failures', params: { days: { label: 'Window (days)', min: 1, max: 90 } } },
}
