export interface NavItem { to: string, label: string, icon: string, permission: Permission, badge?: 'alerts' }

/** Sidebar, filtered per role. */
export const NAV: { group: string, items: NavItem[] }[] = [
  { group: 'General', items: [
    { to: '/dashboard', label: 'Overview', icon: 'bi-speedometer2', permission: 'dashboard:exec' },
    { to: '/alerts', label: 'Alerts', icon: 'bi-bell', permission: 'alerts:read', badge: 'alerts' },
    { to: '/reports/board-pack', label: 'Board report', icon: 'bi-file-earmark-bar-graph', permission: 'reports:board' },
  ] },
  { group: 'Monitoring', items: [
    { to: '/ccm', label: 'Control monitoring', icon: 'bi-broadcast', permission: 'ccm:read' },
    { to: '/controls', label: 'Controls', icon: 'bi-shield-check', permission: 'controls:read' },
    { to: '/integrations', label: 'Integrations', icon: 'bi-diagram-3', permission: 'integrations:read' },
  ] },
  { group: 'Risk and compliance', items: [
    { to: '/risks', label: 'Risk register', icon: 'bi-grid-3x3', permission: 'risk:read' },
    { to: '/compliance', label: 'Compliance', icon: 'bi-journal-check', permission: 'compliance:read' },
    { to: '/governance', label: 'Policies', icon: 'bi-bank', permission: 'governance:read' },
    { to: '/esg', label: 'ESG', icon: 'bi-tree', permission: 'esg:read' },
  ] },
  { group: 'Assurance', items: [
    { to: '/assurance', label: 'Assurance map', icon: 'bi-layers', permission: 'assurance:read' },
    { to: '/audit', label: 'Internal audit', icon: 'bi-clipboard-check', permission: 'audit:read' },
    { to: '/csa', label: 'Self-assessments', icon: 'bi-ui-checks', permission: 'csa:read' },
  ] },
  { group: 'Financial crime', items: [
    { to: '/aml', label: 'Cases and screening', icon: 'bi-search', permission: 'aml:read' },
  ] },
  { group: 'Operations', items: [
    { to: '/incidents', label: 'Incidents', icon: 'bi-lightning', permission: 'incidents:read' },
    { to: '/tprm', label: 'Third parties', icon: 'bi-building', permission: 'tprm:read' },
    { to: '/vetting', label: 'Vetting', icon: 'bi-person-badge', permission: 'hr:read' },
    { to: '/security', label: 'Access reviews', icon: 'bi-key', permission: 'security:read' },
  ] },
  { group: 'Delivery', items: [
    { to: '/programme', label: 'Implementation', icon: 'bi-kanban', permission: 'programme:read' },
  ] },
  { group: 'Admin', items: [
    { to: '/admin/users', label: 'Users', icon: 'bi-people', permission: 'users:manage' },
    { to: '/admin/audit-log', label: 'Audit log', icon: 'bi-journal-text', permission: 'auditlog:read' },
  ] },
]
