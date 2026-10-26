// SLA and escalation primitives for CCM alerts, spec §4.4, §5 and §7.2.
import type { RoleKey } from './rbac'

export type Severity = 'critical' | 'high' | 'medium' | 'low'
export const SEVERITIES: Severity[] = ['critical', 'high', 'medium', 'low']

export type SlaSpec =
  | { type: 'minutes', minutes: number }
  | { type: 'business_days', days: number }
  | { type: 'end_of_day' }
  | { type: 'immediate' }
  /** Due a fixed time before the breached entity's own deadline (e.g. CCM-008 "T-1 day"). */
  | { type: 'before_entity_due', minutes: number }

/** §7.2: Critical alerts must be acknowledged within 30 minutes. Other severities scale from there. */
export const ACK_MINUTES: Record<Severity, number> = { critical: 30, high: 120, medium: 480, low: 1440 }

const MIN = 60_000

export function addBusinessDays(from: Date, days: number) {
  // Mon–Fri working week. UK bank holidays are not modelled yet.
  const d = new Date(from)
  let left = days
  while (left > 0) {
    d.setDate(d.getDate() + 1)
    const day = d.getDay()
    if (day !== 0 && day !== 6) left--
  }
  return d
}

export function computeDueAt(sla: SlaSpec, detectedAt: Date, entityDueAt?: Date | null): Date {
  switch (sla.type) {
    case 'minutes': return new Date(detectedAt.getTime() + sla.minutes * MIN)
    case 'business_days': return addBusinessDays(detectedAt, sla.days)
    case 'end_of_day': { const d = new Date(detectedAt); d.setHours(23, 59, 59, 0); return d }
    case 'immediate': return new Date(detectedAt)
    case 'before_entity_due': {
      const due = entityDueAt ? new Date(entityDueAt.getTime() - sla.minutes * MIN) : detectedAt
      return due < detectedAt ? new Date(detectedAt) : due
    }
  }
}

/** Length of one escalation step once an alert is past its SLA. */
export function escalationStepMs(sla: SlaSpec): number {
  switch (sla.type) {
    case 'minutes': return Math.max(sla.minutes, 30) * MIN
    case 'business_days': return 24 * 60 * MIN
    case 'end_of_day': return 4 * 60 * MIN
    case 'immediate': return 60 * MIN
    case 'before_entity_due': return 12 * 60 * MIN
  }
}

export function slaLabel(sla: SlaSpec): string {
  switch (sla.type) {
    case 'minutes': return sla.minutes < 60 ? `${sla.minutes} minutes` : `${sla.minutes / 60} hour${sla.minutes === 60 ? '' : 's'}`
    case 'business_days': return `${sla.days} business day${sla.days === 1 ? '' : 's'}`
    case 'end_of_day': return 'Same day'
    case 'immediate': return 'Immediate'
    case 'before_entity_due': return `T-${Math.round(sla.minutes / 1440)} day`
  }
}

// Escalation targets named in §5. Some map to platform roles (users get in-app notifications);
// the rest are external bodies that are notified out-of-band and recorded on the alert timeline.
export type EscalationTarget =
  | 'CONTROL_OWNER' | 'BU_MANAGER' | 'COMPLIANCE' | 'MLRO' | 'CCO' | 'CRO' | 'LEGAL' | 'HR'
  | 'CISO' | 'CTO' | 'HEAD_OF_AUDIT' | 'BOARD_AUDIT_COMMITTEE' | 'STEERING_COMMITTEE'

export const ESCALATION_TARGETS: Record<EscalationTarget, { label: string, role?: RoleKey, dynamic?: 'owner' | 'bu_manager' }> = {
  CONTROL_OWNER: { label: 'Control owner', dynamic: 'owner' },
  BU_MANAGER: { label: '1st line manager', role: 'BU_MANAGER', dynamic: 'bu_manager' },
  COMPLIANCE: { label: 'Compliance', role: 'COMPLIANCE_OFFICER' },
  MLRO: { label: 'MLRO', role: 'MLRO' },
  CCO: { label: 'CCO', role: 'CCO' },
  CRO: { label: 'CRO', role: 'CRO' },
  LEGAL: { label: 'Legal' },
  HR: { label: 'HR', role: 'HR' },
  CISO: { label: 'CISO', role: 'CISO' },
  CTO: { label: 'CTO' },
  HEAD_OF_AUDIT: { label: 'Head of Audit', role: 'AUDIT' },
  BOARD_AUDIT_COMMITTEE: { label: 'Board Audit Committee', role: 'BOARD' },
  STEERING_COMMITTEE: { label: 'Steering Committee', role: 'BOARD' },
}

export const escalationPathLabel = (path: EscalationTarget[]) => path.map(t => ESCALATION_TARGETS[t].label).join(' → ')
