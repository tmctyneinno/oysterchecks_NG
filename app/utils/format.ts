type DateLike = string | number | Date | null | undefined

const toDate = (d: DateLike) => (d == null ? null : d instanceof Date ? d : new Date(d))

export const fmtDate = (d: DateLike) => {
  const x = toDate(d)
  return x ? x.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
}

export const fmtDateTime = (d: DateLike) => {
  const x = toDate(d)
  return x ? x.toLocaleString('en-GB', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-'
}

export const fmtTime = (d: DateLike) => {
  const x = toDate(d)
  return x ? x.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '-'
}

export function fmtRelative(d: DateLike, now = Date.now()) {
  const x = toDate(d)
  if (!x) return '-'
  const diff = x.getTime() - now
  const abs = Math.abs(diff)
  const rtf = new Intl.RelativeTimeFormat('en-GB', { numeric: 'auto' })
  if (abs < 60_000) return diff < 0 ? 'just now' : 'in under a minute'
  if (abs < 3_600_000) return rtf.format(Math.round(diff / 60_000), 'minute')
  if (abs < 86_400_000) return rtf.format(Math.round(diff / 3_600_000), 'hour')
  if (abs < 30 * 86_400_000) return rtf.format(Math.round(diff / 86_400_000), 'day')
  return fmtDate(x)
}

/** Compact from 1,000 up (dataviz guidance): 1,284 → 1.3K, 4,200,000 → 4.2M. */
export const fmtCompact = (n: number) => new Intl.NumberFormat('en-GB', { notation: 'compact', maximumFractionDigits: 1 }).format(n)
export const fmtNumber = (n: number | null | undefined) => (n == null ? '-' : new Intl.NumberFormat('en-GB').format(n))
export const fmtGBP = (n: number | null | undefined, compact = false) =>
  n == null ? '-' : new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP', maximumFractionDigits: 0, notation: compact ? 'compact' : 'standard' }).format(n)

const ACRONYMS = new Set(['kyc', 'kyb', 'edd', 'sar', 'ccm', 'aml', 'kri', 'csa', 'pep', 'nca', 'sla', 'esg', 'mfa', 'dpa', 'rca'])

/** 'partially_effective' to 'Partially effective'; known acronyms stay upper case ('sar_decided' to 'SAR decided'). */
export const labelize = (s: string | null | undefined) => {
  if (!s) return '-'
  const words = s.split('_').map(w => (ACRONYMS.has(w.toLowerCase()) ? w.toUpperCase() : w.toLowerCase()))
  const first = words[0]!
  words[0] = first === first.toUpperCase() ? first : first.charAt(0).toUpperCase() + first.slice(1)
  return words.join(' ')
}

/** plural(3, 'control') is '3 controls'; plural(1, 'policy', 'policies') is '1 policy'. */
export const plural = (n: number, one: string, many = `${one}s`) => `${n} ${n === 1 ? one : many}`

export type Tone = 'critical' | 'high' | 'medium' | 'low' | 'ok' | 'neutral' | 'purple'

/** Single mapping from domain states to status tones, so colour meaning stays consistent everywhere. */
export function toneFor(value: string | null | undefined): Tone {
  switch (value) {
    case 'critical': case 'fail': case 'ineffective': case 'non_compliant': case 'overdue': case 'off_track': case 'red':
    case 'unsatisfactory': case 'confirmed_match': case 'failed': case 'gap': case 'escalated': case 'hit':
      return 'critical'
    case 'high': case 'warn': case 'partially_effective': case 'partial': case 'due_soon': case 'at_risk': case 'amber':
    case 'needs_improvement': case 'potential_match': case 'pending_closure': case 'in_review': case 'thin': case 'sar_decided':
      return 'high'
    case 'medium': case 'investigating': case 'acknowledged': case 'in_progress': case 'under_review': case 'fieldwork':
    case 'reporting': case 'remediating': case 'draft': case 'upcoming': case 'enhanced':
      return 'medium'
    case 'pass': case 'effective': case 'compliant': case 'on_track': case 'green': case 'satisfactory': case 'clear': case 'cleared':
    case 'complete': case 'approved': case 'closed': case 'submitted': case 'verified': case 'done': case 'adequate': case 'sar_submitted':
      return 'ok'
    case 'over': return 'purple'
    default: return 'neutral'
  }
}

export const SEVERITY_ICON: Record<string, string> = {
  critical: 'bi-exclamation-octagon-fill',
  high: 'bi-exclamation-triangle-fill',
  medium: 'bi-info-circle-fill',
  low: 'bi-dash-circle',
}
