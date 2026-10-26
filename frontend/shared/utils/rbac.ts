// Role-Based Access Control, spec §1.3 (target users / landing screens) and §6.5 (RBAC matrix).
// This file is the single source of truth: the server enforces it on every API call, the client
// uses it only to decide what to render.

export const ROLE_KEYS = [
  'CRO', 'CCO', 'MLRO', 'AML_ANALYST', 'COMPLIANCE_OFFICER', 'AUDIT', 'CISO', 'HR', 'BU_MANAGER', 'BOARD', 'ADMIN',
] as const
export type RoleKey = typeof ROLE_KEYS[number]

export const PERMISSIONS = [
  'dashboard:exec', 'finance:read', 'reports:board',
  'alerts:read', 'alerts:act', 'alerts:close',
  'ccm:read', 'ccm:manage',
  'risk:read', 'risk:write',
  'controls:read', 'controls:write',
  'compliance:read', 'compliance:write', 'regreport:submit',
  'governance:read', 'governance:write', 'policy:approve',
  'assurance:read', 'assurance:write',
  'audit:read', 'audit:write',
  'csa:read', 'csa:submit',
  'aml:read', 'aml:write', 'sar:submit',
  'hr:read', 'hr:write',
  'security:read', 'security:write',
  'tprm:read', 'tprm:write',
  'incidents:read', 'incidents:write',
  'esg:read', 'esg:write',
  'programme:read', 'programme:write',
  'integrations:read', 'integrations:manage',
  'auditlog:read', 'users:manage', 'pii:view',
] as const
export type Permission = typeof PERMISSIONS[number]

const ALL_READ = PERMISSIONS.filter(p => p.endsWith(':read')) as Permission[]
const without = (list: Permission[], ...drop: Permission[]) => list.filter(p => !drop.includes(p))

export interface RoleDefinition {
  key: RoleKey
  label: string
  /** Landing screen after sign-in (§1.3). */
  landing: string
  /** Data scope. 'bu' = only records belonging to the user's business unit (§6.5: "Own BU controls and alerts only"). */
  scope: 'all' | 'bu'
  permissions: Permission[]
  restrictedNote: string
}

export const ROLES: Record<RoleKey, RoleDefinition> = {
  CRO: {
    key: 'CRO', label: 'Chief Risk Officer', landing: '/dashboard', scope: 'all',
    permissions: [...ALL_READ, 'dashboard:exec', 'reports:board', 'risk:write', 'alerts:act', 'alerts:close', 'assurance:write', 'pii:view'],
    restrictedNote: 'None (full read access)',
  },
  CCO: {
    key: 'CCO', label: 'Chief Compliance Officer', landing: '/compliance', scope: 'all',
    permissions: [
      ...without(ALL_READ, 'finance:read' as Permission),
      'dashboard:exec', 'reports:board', 'alerts:act', 'alerts:close', 'ccm:manage', 'compliance:write', 'regreport:submit',
      'governance:write', 'policy:approve', 'controls:write', 'incidents:write', 'tprm:write', 'esg:write', 'assurance:write', 'pii:view',
    ],
    restrictedNote: 'Executive financial metrics',
  },
  MLRO: {
    key: 'MLRO', label: 'MLRO / AML Lead', landing: '/aml', scope: 'all',
    permissions: [
      'alerts:read', 'alerts:act', 'alerts:close', 'ccm:read', 'risk:read', 'controls:read', 'compliance:read', 'governance:read',
      'aml:read', 'aml:write', 'sar:submit', 'incidents:read', 'incidents:write', 'tprm:read', 'pii:view',
    ],
    restrictedNote: 'Board-level financials; HR data',
  },
  AML_ANALYST: {
    key: 'AML_ANALYST', label: 'AML Analyst', landing: '/aml', scope: 'all',
    permissions: ['alerts:read', 'alerts:act', 'ccm:read', 'aml:read', 'aml:write', 'governance:read', 'incidents:read', 'incidents:write', 'pii:view'],
    restrictedNote: 'SAR submission; HR data; financials',
  },
  COMPLIANCE_OFFICER: {
    key: 'COMPLIANCE_OFFICER', label: 'Compliance Officer', landing: '/alerts', scope: 'all',
    permissions: [
      'alerts:read', 'alerts:act', 'ccm:read', 'risk:read', 'controls:read', 'compliance:read', 'compliance:write', 'governance:read',
      'assurance:read', 'csa:read', 'aml:read', 'incidents:read', 'incidents:write', 'tprm:read', 'tprm:write', 'esg:read', 'pii:view',
    ],
    restrictedNote: 'Regulatory submission; HR data; financials',
  },
  AUDIT: {
    key: 'AUDIT', label: 'Head of Internal Audit', landing: '/assurance', scope: 'all',
    permissions: [
      ...without(ALL_READ, 'aml:read', 'hr:read', 'finance:read' as Permission),
      'dashboard:exec', 'reports:board', 'alerts:act', 'assurance:write', 'audit:write',
    ],
    restrictedNote: 'AML case details; HR vetting data',
  },
  CISO: {
    key: 'CISO', label: 'CISO', landing: '/ccm', scope: 'all',
    permissions: [
      'alerts:read', 'alerts:act', 'alerts:close', 'ccm:read', 'risk:read', 'controls:read', 'controls:write', 'compliance:read',
      'governance:read', 'security:read', 'security:write', 'incidents:read', 'incidents:write', 'tprm:read', 'integrations:read',
    ],
    restrictedNote: 'AML case data; regulatory submissions',
  },
  HR: {
    key: 'HR', label: 'HR Director', landing: '/vetting', scope: 'all',
    permissions: ['alerts:read', 'alerts:act', 'governance:read', 'hr:read', 'hr:write', 'incidents:read', 'incidents:write', 'pii:view'],
    restrictedNote: 'Financial/AML data; risk register',
  },
  BU_MANAGER: {
    key: 'BU_MANAGER', label: '1st Line BU Manager', landing: '/csa', scope: 'bu',
    permissions: ['alerts:read', 'alerts:act', 'controls:read', 'governance:read', 'csa:read', 'csa:submit', 'incidents:read', 'incidents:write'],
    restrictedNote: 'All 2nd/3rd line views; AML',
  },
  BOARD: {
    key: 'BOARD', label: 'Board / Executive Committee', landing: '/dashboard', scope: 'all',
    permissions: ['dashboard:exec', 'finance:read', 'reports:board', 'alerts:read', 'risk:read', 'compliance:read', 'assurance:read', 'esg:read', 'programme:read'],
    restrictedNote: 'Operational case data; PII',
  },
  ADMIN: {
    key: 'ADMIN', label: 'OysterChecks Admin', landing: '/admin/users', scope: 'all',
    permissions: [...ALL_READ, 'ccm:manage', 'programme:write', 'integrations:manage', 'users:manage'],
    restrictedNote: 'Customer/employee PII (masked)',
  },
}

export const can = (role: RoleKey | undefined | null, permission: Permission) =>
  !!role && ROLES[role]?.permissions.includes(permission)

export const BUSINESS_UNITS = ['Retail Banking', 'Corporate Banking', 'Payments', 'Operations', 'Technology', 'People'] as const
export type BusinessUnit = typeof BUSINESS_UNITS[number]
