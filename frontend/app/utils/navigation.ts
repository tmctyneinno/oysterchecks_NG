export interface NavItem { to: string, label: string, icon: string, permission: Permission, badge?: 'alerts', exact?: boolean }

/**
 * Sidebar, filtered per role. Groups follow the Version 1.2 customer navigation. The delivery
 * programme is an internal view, so it sits under Administration and only delivery and programme
 * governance roles can see it.
 */
export const NAV: { group: string, items: NavItem[] }[] = [
  { group: 'Executive intelligence', items: [
    { to: '/dashboard', label: 'Overview', icon: 'bi-speedometer2', permission: 'dashboard:exec' },
  ] },
  { group: 'Perimeter intelligence', items: [
    { to: '/perimeter', label: 'Coverage and sources', icon: 'bi-globe2', permission: 'perimeter:read', exact: true },
    { to: '/perimeter/changes', label: 'Regulatory change', icon: 'bi-arrow-left-right', permission: 'perimeter:read' },
  ] },
  { group: 'Regulatory obligations', items: [
    { to: '/compliance', label: 'Obligations and returns', icon: 'bi-journal-check', permission: 'compliance:read' },
    { to: '/governance', label: 'Policies', icon: 'bi-bank', permission: 'governance:read' },
  ] },
  { group: 'Enterprise risk', items: [
    { to: '/risks', label: 'Risk register', icon: 'bi-grid-3x3', permission: 'risk:read' },
  ] },
  { group: 'Controls and CCM', items: [
    { to: '/alerts', label: 'Alerts', icon: 'bi-bell', permission: 'alerts:read', badge: 'alerts' },
    { to: '/ccm', label: 'Control monitoring', icon: 'bi-broadcast', permission: 'ccm:read' },
    { to: '/controls', label: 'Controls', icon: 'bi-shield-check', permission: 'controls:read' },
    { to: '/security', label: 'Access reviews', icon: 'bi-key', permission: 'security:read' },
    { to: '/vetting', label: 'Vetting', icon: 'bi-person-badge', permission: 'hr:read' },
  ] },
  { group: 'Financial crime', items: [
    { to: '/aml', label: 'Customers and screening', icon: 'bi-person-vcard', permission: 'aml:read' },
  ] },
  { group: 'Transaction monitoring', items: [
    { to: '/transaction-monitoring', label: 'Alert queues and scenarios', icon: 'bi-activity', permission: 'tm:read' },
  ] },
  { group: 'Digital assets intelligence', items: [
    { to: '/digital-assets', label: 'Wallets and Travel Rule', icon: 'bi-currency-bitcoin', permission: 'crypto:read' },
  ] },
  { group: 'Cases and investigations', items: [
    { to: '/cases', label: 'Cases', icon: 'bi-search', permission: 'aml:read' },
  ] },
  { group: 'Third party risk', items: [
    { to: '/tprm', label: 'Third parties', icon: 'bi-building', permission: 'tprm:read' },
  ] },
  { group: 'Incidents and remediation', items: [
    { to: '/incidents', label: 'Incidents and actions', icon: 'bi-lightning', permission: 'incidents:read' },
  ] },
  { group: 'Three lines assurance', items: [
    { to: '/assurance', label: 'Assurance map', icon: 'bi-layers', permission: 'assurance:read' },
    { to: '/audit', label: 'Internal audit', icon: 'bi-clipboard-check', permission: 'audit:read' },
    { to: '/csa', label: 'Self-assessments', icon: 'bi-ui-checks', permission: 'csa:read' },
  ] },
  { group: 'Evidence and reporting', items: [
    { to: '/reports/board-pack', label: 'Board report', icon: 'bi-file-earmark-bar-graph', permission: 'reports:board' },
    { to: '/esg', label: 'ESG', icon: 'bi-tree', permission: 'esg:read' },
  ] },
  { group: 'Administration and integrations', items: [
    { to: '/integrations', label: 'Integrations', icon: 'bi-diagram-3', permission: 'integrations:read' },
    { to: '/admin/users', label: 'Users', icon: 'bi-people', permission: 'users:manage' },
    { to: '/admin/audit-log', label: 'Audit log', icon: 'bi-journal-text', permission: 'auditlog:read' },
    { to: '/programme', label: 'Delivery programme (internal)', icon: 'bi-kanban', permission: 'programme:read' },
  ] },
]
