// Hardcoded demo data for the Version 1.2 capabilities: Perimeter Intelligence (legal entities,
// jurisdiction packs, regulatory sources, regulatory change), multi-channel transaction monitoring,
// Digital Assets Intelligence and the pre-initiation delivery gates.
//
// Regulatory content is illustrative: real instruments are named so the demo reads credibly, but
// summaries are simplified and must not be relied on as legal content. Dates are relative to now.

const DAY = 86_400_000
const ago = (days: number, hours = 0) => new Date(Date.now() - days * DAY - hours * 3_600_000).toISOString()
const ahead = (days: number) => new Date(Date.now() + days * DAY).toISOString()

/** Deterministic 64-hex fingerprint for demo provenance records. */
export function demoHash(text: string) {
  let out = ''
  for (let seed = 0; out.length < 64; seed++) {
    let h = 0x811C9DC5 ^ seed
    for (let i = 0; i < text.length; i++) h = Math.imul(h ^ text.charCodeAt(i), 0x01000193)
    out += (h >>> 0).toString(16).padStart(8, '0')
  }
  return out.slice(0, 64)
}

// ── Legal entities of the client group ─────────────────────────────────────────────────────

export interface LegalEntity {
  id: string, name: string, jurisdiction: string, country: string, regulators: string[], activities: string[]
  /** The UK bank is where the rest of the demo's records live; other entities carry summary figures. */
  primary: boolean
  summary: { openAlerts: number, highRisks: number, failingControls: number, openCases: number, assurance: string }
}

export const LEGAL_ENTITIES: LegalEntity[] = [
  { id: 'E1', name: 'Ajala Bank UK Ltd', jurisdiction: 'GB', country: 'United Kingdom', regulators: ['PRA', 'FCA', 'ICO', 'NCA'], activities: ['Retail banking', 'Corporate banking', 'Payments'], primary: true, summary: { openAlerts: 0, highRisks: 0, failingControls: 0, openCases: 0, assurance: 'needs_improvement' } },
  { id: 'E2', name: 'Ajala Bank Nigeria Plc', jurisdiction: 'NG', country: 'Nigeria', regulators: ['CBN', 'NFIU', 'NDPC'], activities: ['Retail banking', 'Mobile money', 'Payments'], primary: false, summary: { openAlerts: 7, highRisks: 3, failingControls: 2, openCases: 4, assurance: 'needs_improvement' } },
  { id: 'E3', name: 'Ajala Remittance LLC', jurisdiction: 'AE', country: 'United Arab Emirates', regulators: ['CBUAE', 'UAE FIU'], activities: ['Remittance', 'Foreign exchange'], primary: false, summary: { openAlerts: 3, highRisks: 1, failingControls: 0, openCases: 1, assurance: 'satisfactory' } },
  { id: 'E4', name: 'Ajala Digital Assets Ltd', jurisdiction: 'NG', country: 'Nigeria', regulators: ['SEC Nigeria', 'NFIU'], activities: ['Virtual asset exchange', 'Custody'], primary: false, summary: { openAlerts: 5, highRisks: 2, failingControls: 1, openCases: 2, assurance: 'not_assessed' } },
  { id: 'E5', name: 'Ajala Payments Ireland Ltd', jurisdiction: 'IE', country: 'Ireland (EU)', regulators: ['Central Bank of Ireland', 'DPC', 'FIU Ireland'], activities: ['Payments', 'E-money'], primary: false, summary: { openAlerts: 2, highRisks: 1, failingControls: 0, openCases: 0, assurance: 'satisfactory' } },
]

// ── Jurisdiction packs ─────────────────────────────────────────────────────────────────────

export interface JurisdictionPack {
  code: string, name: string, status: 'active' | 'available', regulators: string[]
  language: string, currency: string, timeZone: string, dataResidency: string
  suspiciousReport: string, breachNotice: string
  sources: number, obligations: number, lastReviewedAt: string, freshnessDays: number, quality: number, coverage: number
}

export const JURISDICTION_PACKS: JurisdictionPack[] = [
  { code: 'GB', name: 'United Kingdom', status: 'active', regulators: ['FCA', 'PRA', 'ICO', 'NCA', 'OFSI'], language: 'English', currency: 'GBP', timeZone: 'Europe/London', dataResidency: 'UK hosting; EU disaster recovery', suspiciousReport: 'SAR to NCA', breachNotice: 'ICO within 72 hours', sources: 6, obligations: 14, lastReviewedAt: ago(3), freshnessDays: 3, quality: 98, coverage: 100 },
  { code: 'NG', name: 'Nigeria', status: 'active', regulators: ['CBN', 'NFIU', 'NDPC', 'SEC Nigeria'], language: 'English', currency: 'NGN', timeZone: 'Africa/Lagos', dataResidency: 'In-country copy required for some data sets', suspiciousReport: 'STR to NFIU within 24 hours', breachNotice: 'NDPC within 72 hours', sources: 6, obligations: 11, lastReviewedAt: ago(9), freshnessDays: 9, quality: 94, coverage: 86 },
  { code: 'AE', name: 'United Arab Emirates', status: 'active', regulators: ['CBUAE', 'UAE FIU', 'UAE Data Office'], language: 'Arabic, English', currency: 'AED', timeZone: 'Asia/Dubai', dataResidency: 'Regional hosting', suspiciousReport: 'STR to UAE FIU via goAML', breachNotice: 'UAE Data Office without delay', sources: 3, obligations: 6, lastReviewedAt: ago(21), freshnessDays: 21, quality: 91, coverage: 72 },
  { code: 'EU', name: 'European Union (Ireland)', status: 'active', regulators: ['Central Bank of Ireland', 'DPC', 'FIU Ireland', 'EBA'], language: 'English, Irish', currency: 'EUR', timeZone: 'Europe/Dublin', dataResidency: 'EU hosting', suspiciousReport: 'STR to FIU Ireland and Revenue', breachNotice: 'DPC within 72 hours', sources: 5, obligations: 9, lastReviewedAt: ago(6), freshnessDays: 6, quality: 96, coverage: 90 },
  { code: 'INT', name: 'International standards', status: 'active', regulators: ['FATF', 'Basel Committee'], language: 'English', currency: 'n/a', timeZone: 'n/a', dataResidency: 'n/a', suspiciousReport: 'Set nationally', breachNotice: 'Set nationally', sources: 1, obligations: 4, lastReviewedAt: ago(14), freshnessDays: 14, quality: 97, coverage: 100 },
  { code: 'KE', name: 'Kenya', status: 'available', regulators: ['CBK', 'FRC Kenya', 'ODPC'], language: 'English, Swahili', currency: 'KES', timeZone: 'Africa/Nairobi', dataResidency: 'Varies by data set', suspiciousReport: 'STR to FRC', breachNotice: 'ODPC within 72 hours', sources: 0, obligations: 0, lastReviewedAt: ago(40), freshnessDays: 40, quality: 0, coverage: 0 },
  { code: 'GH', name: 'Ghana', status: 'available', regulators: ['Bank of Ghana', 'FIC Ghana'], language: 'English', currency: 'GHS', timeZone: 'Africa/Accra', dataResidency: 'Varies by data set', suspiciousReport: 'STR to FIC', breachNotice: 'Data Protection Commission', sources: 0, obligations: 0, lastReviewedAt: ago(40), freshnessDays: 40, quality: 0, coverage: 0 },
  { code: 'ZA', name: 'South Africa', status: 'available', regulators: ['SARB', 'FIC', 'Information Regulator'], language: 'English', currency: 'ZAR', timeZone: 'Africa/Johannesburg', dataResidency: 'Varies by data set', suspiciousReport: 'STR to FIC', breachNotice: 'Information Regulator as soon as reasonably possible', sources: 0, obligations: 0, lastReviewedAt: ago(40), freshnessDays: 40, quality: 0, coverage: 0 },
]

// ── Regulatory source inventory with provenance ────────────────────────────────────────────

export interface RegulatorySource {
  id: number, ref: string, title: string, issuer: string, jurisdiction: string, type: 'law' | 'regulation' | 'guidance' | 'standard'
  url: string, version: string, retrievedAt: string, checkedAt: string, sha256: string, status: 'current' | 'superseded'
  obligationIds: number[]
}

const src = (id: number, ref: string, title: string, issuer: string, jurisdiction: string, type: RegulatorySource['type'], url: string, version: string, checked: number, obligationIds: number[] = []): RegulatorySource =>
  ({ id, ref, title, issuer, jurisdiction, type, url, version, retrievedAt: ago(checked + 30), checkedAt: ago(checked), sha256: demoHash(ref + version), status: 'current', obligationIds })

export const SOURCES: RegulatorySource[] = [
  src(1, 'SRC-GB-001', 'Money Laundering, Terrorist Financing and Transfer of Funds Regulations 2017', 'UK Parliament', 'GB', 'regulation', 'legislation.gov.uk', 'As amended', 2, [1]),
  src(2, 'SRC-GB-002', 'FCA Handbook: SYSC 6.3 Financial crime', 'FCA', 'GB', 'regulation', 'handbook.fca.org.uk', 'Current release', 3, [2]),
  src(3, 'SRC-GB-003', 'Proceeds of Crime Act 2002, Part 7', 'UK Parliament', 'GB', 'law', 'legislation.gov.uk', 'As amended', 2, [3]),
  src(4, 'SRC-GB-004', 'UK GDPR and Data Protection Act 2018', 'UK Parliament / ICO', 'GB', 'law', 'legislation.gov.uk', 'As amended', 5, [4, 5]),
  src(5, 'SRC-GB-005', 'PRA Supervisory Statement SS1/21 Operational resilience', 'PRA', 'GB', 'guidance', 'bankofengland.co.uk', 'SS1/21', 4, [6]),
  src(6, 'SRC-GB-006', 'Economic Crime and Corporate Transparency Act 2023 (failure to prevent fraud)', 'UK Parliament', 'GB', 'law', 'legislation.gov.uk', 'As enacted', 3),
  src(7, 'SRC-NG-001', 'Money Laundering (Prevention and Prohibition) Act 2022', 'National Assembly of Nigeria', 'NG', 'law', 'nfiu.gov.ng', '2022', 8),
  src(8, 'SRC-NG-002', 'CBN AML/CFT/CPF Regulations for Banks and Other Financial Institutions 2022', 'Central Bank of Nigeria', 'NG', 'regulation', 'cbn.gov.ng', '2022', 9),
  src(9, 'SRC-NG-003', 'Nigeria Data Protection Act 2023', 'National Assembly of Nigeria', 'NG', 'law', 'ndpc.gov.ng', '2023', 11),
  src(10, 'SRC-NG-004', 'NDPC General Application and Implementation Directive', 'Nigeria Data Protection Commission', 'NG', 'guidance', 'ndpc.gov.ng', '2025', 11),
  src(11, 'SRC-NG-005', 'Investments and Securities Act 2025 (digital assets as securities)', 'National Assembly of Nigeria', 'NG', 'law', 'sec.gov.ng', '2025', 6),
  src(12, 'SRC-NG-006', 'SEC Rules on Issuance, Offering Platforms and Custody of Digital Assets', 'SEC Nigeria', 'NG', 'regulation', 'sec.gov.ng', '2022', 12),
  src(13, 'SRC-AE-001', 'CBUAE AML/CFT Standards and Guidance for Licensed Financial Institutions', 'Central Bank of the UAE', 'AE', 'guidance', 'centralbank.ae', 'Current', 20),
  src(14, 'SRC-AE-002', 'UAE Personal Data Protection Law (Federal Decree-Law No. 45 of 2021)', 'UAE Government', 'AE', 'law', 'u.ae', '2021', 22),
  src(15, 'SRC-AE-003', 'UAE FIU goAML reporting guidance', 'UAE Financial Intelligence Unit', 'AE', 'guidance', 'uaefiu.gov.ae', 'Current', 25),
  src(16, 'SRC-EU-001', 'Markets in Crypto-Assets Regulation (EU) 2023/1114', 'European Union', 'EU', 'regulation', 'eur-lex.europa.eu', 'OJ L 150', 6),
  src(17, 'SRC-EU-002', 'Transfer of Funds Regulation (EU) 2023/1113 (crypto Travel Rule)', 'European Union', 'EU', 'regulation', 'eur-lex.europa.eu', 'OJ L 150', 6),
  src(18, 'SRC-EU-003', 'Anti-Money Laundering Regulation (EU) 2024/1624', 'European Union', 'EU', 'regulation', 'eur-lex.europa.eu', 'OJ L 2024/1624', 7),
  src(19, 'SRC-EU-004', 'Digital Operational Resilience Act (EU) 2022/2554', 'European Union', 'EU', 'regulation', 'eur-lex.europa.eu', 'OJ L 333', 8),
  src(20, 'SRC-EU-005', 'General Data Protection Regulation (EU) 2016/679', 'European Union', 'EU', 'regulation', 'eur-lex.europa.eu', 'OJ L 119', 9),
  src(21, 'SRC-INT-001', 'FATF Recommendations (including R.15 virtual assets and R.16 payment transparency)', 'FATF', 'INT', 'standard', 'fatf-gafi.org', 'Current edition', 14, [10]),
]

/** Obligation (existing register) → regulatory source, for the traceability chain. */
export const OBLIGATION_SOURCE: Record<number, number> = { 1: 1, 2: 2, 3: 3, 4: 4, 5: 4, 6: 5, 10: 21 }

// ── Regulatory change ──────────────────────────────────────────────────────────────────────

export type ChangeStatus = 'new' | 'triage' | 'applicability' | 'pending_approval' | 'approved' | 'returned'
export type Applicability = 'applicable' | 'partially_applicable' | 'not_applicable'

export interface RegulatoryChange {
  id: number, ref: string, title: string, issuer: string, jurisdiction: string, sourceId: number
  publishedAt: string, effectiveAt: string | null, priority: 'high' | 'medium' | 'low', status: ChangeStatus
  summary: string
  /** Requirements drafted by automated extraction. A person must validate each before use. */
  extracted: { id: number, text: string, validated: boolean }[]
  applicability: { entityId: string, decision: Applicability | null, rationale: string | null }[]
  impacts: { obligationIds: number[], controlIds: number[], policyIds: number[], riskIds: number[] }
  actions: { id: number, title: string, ownerId: number, dueAt: string, status: 'open' | 'done' }[]
  makerId: number | null, approverId: number | null
  history: { at: string, actorName: string, action: string, note: string | null }[]
}

const applies = (decisions: Record<string, [Applicability | null, string | null]>) =>
  LEGAL_ENTITIES.map(e => ({ entityId: e.id, decision: decisions[e.id]?.[0] ?? null, rationale: decisions[e.id]?.[1] ?? null }))

export const CHANGES: RegulatoryChange[] = [
  {
    id: 1, ref: 'RC-0101', title: 'FATF revision of Recommendation 16 on payment transparency', issuer: 'FATF', jurisdiction: 'INT', sourceId: 21,
    publishedAt: ago(140), effectiveAt: null, priority: 'high', status: 'pending_approval',
    summary: 'Revised standard for originator and beneficiary information in cross-border payments and value transfers, including clearer responsibilities across the payment chain and alignment of card and virtual-asset transfers. National implementation is expected over several years.',
    extracted: [
      { id: 1, text: 'Include structured originator and beneficiary information with cross-border transfers above the applicable threshold.', validated: true },
      { id: 2, text: 'Verify beneficiary account details against the payee name before executing the transfer, where available.', validated: true },
      { id: 3, text: 'Apply the same information requirements to virtual-asset transfers (Travel Rule).', validated: true },
    ],
    applicability: applies({ E1: ['applicable', 'Cross-border payments from UK accounts.'], E2: ['applicable', 'Cross-border transfers and mobile money remittances.'], E3: ['applicable', 'Core remittance business.'], E4: ['applicable', 'Virtual-asset transfers (Travel Rule).'], E5: ['applicable', 'EU payments and e-money.'] }),
    impacts: { obligationIds: [2, 10], controlIds: [2, 11], policyIds: [2], riskIds: [1, 2] },
    actions: [
      { id: 1, title: 'Gap assessment of payment message fields against revised R.16', ownerId: 5, dueAt: ahead(30), status: 'open' },
      { id: 2, title: 'Update Sanctions Compliance Policy for payee verification', ownerId: 2, dueAt: ahead(60), status: 'open' },
    ],
    makerId: 5, approverId: null,
    history: [
      { at: ago(30), actorName: 'Perimeter Intelligence', action: 'Captured from FATF publication feed', note: null },
      { at: ago(28), actorName: 'Grace Ibekwe', action: 'Extraction validated', note: '3 of 3 requirements confirmed' },
      { at: ago(26), actorName: 'Grace Ibekwe', action: 'Applicability assessed', note: 'Applies to all five entities' },
      { at: ago(25), actorName: 'Grace Ibekwe', action: 'Submitted for approval', note: null },
    ],
  },
  {
    id: 2, ref: 'RC-0102', title: 'Investments and Securities Act 2025: digital assets regulated as securities', issuer: 'National Assembly of Nigeria', jurisdiction: 'NG', sourceId: 11,
    publishedAt: ago(230), effectiveAt: ago(230), priority: 'high', status: 'applicability',
    summary: 'Brings digital assets and virtual asset service providers within the SEC Nigeria regulatory perimeter, with registration, custody and conduct expectations for platforms operating in Nigeria.',
    extracted: [
      { id: 1, text: 'Register virtual asset service activities with SEC Nigeria before operating.', validated: true },
      { id: 2, text: 'Maintain segregated custody of client digital assets.', validated: true },
      { id: 3, text: 'Report suspicious digital-asset transactions to the NFIU.', validated: false },
    ],
    applicability: applies({ E4: ['applicable', 'Operates a virtual asset exchange and custody service in Nigeria.'], E2: ['partially_applicable', null] }),
    impacts: { obligationIds: [], controlIds: [], policyIds: [1], riskIds: [] },
    actions: [],
    makerId: 5, approverId: null,
    history: [
      { at: ago(12), actorName: 'Perimeter Intelligence', action: 'Captured from SEC Nigeria source', note: null },
      { at: ago(10), actorName: 'Grace Ibekwe', action: 'Triaged', note: 'High priority: licensing requirement for E4' },
    ],
  },
  {
    id: 3, ref: 'RC-0103', title: 'NDPC General Application and Implementation Directive', issuer: 'Nigeria Data Protection Commission', jurisdiction: 'NG', sourceId: 10,
    publishedAt: ago(200), effectiveAt: ago(60), priority: 'medium', status: 'triage',
    summary: 'Implementation directive for the Nigeria Data Protection Act 2023, covering compliance audits, data protection officers, breach handling and processing records for data controllers and processors.',
    extracted: [
      { id: 1, text: 'File an annual compliance audit return for qualifying data controllers.', validated: false },
      { id: 2, text: 'Designate a data protection officer with appropriate expertise.', validated: false },
      { id: 3, text: 'Notify the Commission of a personal data breach within 72 hours of awareness.', validated: false },
      { id: 4, text: 'Maintain a record of processing activities.', validated: false },
    ],
    applicability: applies({}),
    impacts: { obligationIds: [], controlIds: [], policyIds: [], riskIds: [] },
    actions: [],
    makerId: null, approverId: null,
    history: [{ at: ago(2), actorName: 'Perimeter Intelligence', action: 'Captured from NDPC source; requirements extracted automatically', note: 'Awaiting human validation' }],
  },
  {
    id: 4, ref: 'RC-0104', title: 'Failure to prevent fraud offence in force', issuer: 'UK Parliament', jurisdiction: 'GB', sourceId: 6,
    publishedAt: ago(420), effectiveAt: ago(440), priority: 'high', status: 'approved',
    summary: 'Large organisations can be criminally liable where an associated person commits fraud intending to benefit the organisation, unless reasonable fraud prevention procedures were in place.',
    extracted: [
      { id: 1, text: 'Maintain reasonable procedures to prevent fraud by associated persons.', validated: true },
      { id: 2, text: 'Conduct and document a fraud risk assessment.', validated: true },
    ],
    applicability: applies({ E1: ['applicable', 'Large organisation under the Act.'], E2: ['not_applicable', 'No UK nexus for this entity.'], E3: ['not_applicable', 'No UK nexus.'], E4: ['not_applicable', 'No UK nexus.'], E5: ['partially_applicable', 'Only where services are provided into the UK.'] }),
    impacts: { obligationIds: [13], controlIds: [15], policyIds: [6], riskIds: [13] },
    actions: [
      { id: 1, title: 'Fraud risk assessment for UK entity', ownerId: 1, dueAt: ago(40), status: 'done' },
      { id: 2, title: 'Extend whistleblowing policy to cover fraud by associated persons', ownerId: 2, dueAt: ago(20), status: 'done' },
    ],
    makerId: 5, approverId: 2,
    history: [
      { at: ago(120), actorName: 'Perimeter Intelligence', action: 'Captured from legislation feed', note: null },
      { at: ago(115), actorName: 'Grace Ibekwe', action: 'Applicability assessed', note: null },
      { at: ago(110), actorName: 'Grace Ibekwe', action: 'Submitted for approval', note: null },
      { at: ago(108), actorName: 'Daniel Okafor', action: 'Approved', note: 'Map to UK Bribery Act controls and run a fraud risk assessment.' },
    ],
  },
  {
    id: 5, ref: 'RC-0105', title: 'EU Anti-Money Laundering Regulation: single rulebook applies', issuer: 'European Union', jurisdiction: 'EU', sourceId: 18,
    publishedAt: ago(500), effectiveAt: ahead(240), priority: 'medium', status: 'new',
    summary: 'Directly applicable EU rulebook for customer due diligence, beneficial ownership and reporting, replacing national transpositions of earlier directives. Horizon item for the Irish entity.',
    extracted: [],
    applicability: applies({}),
    impacts: { obligationIds: [], controlIds: [], policyIds: [], riskIds: [] },
    actions: [],
    makerId: null, approverId: null,
    history: [{ at: ago(1), actorName: 'Perimeter Intelligence', action: 'Added to regulatory horizon', note: null }],
  },
  {
    id: 6, ref: 'RC-0106', title: 'Crypto-asset transfers: Travel Rule under the Transfer of Funds Regulation', issuer: 'European Union', jurisdiction: 'EU', sourceId: 17,
    publishedAt: ago(700), effectiveAt: ago(320), priority: 'high', status: 'approved',
    summary: 'Crypto-asset service providers must send and receive originator and beneficiary information with every crypto-asset transfer, with additional checks for self-hosted wallets.',
    extracted: [
      { id: 1, text: 'Transmit originator and beneficiary information with each crypto-asset transfer.', validated: true },
      { id: 2, text: 'Verify ownership or control of self-hosted wallets above the set threshold.', validated: true },
    ],
    applicability: applies({ E5: ['not_applicable', 'No crypto-asset services in the EU.'], E4: ['partially_applicable', 'Applies to transfers with EU counterparties.'], E1: ['not_applicable', 'No crypto-asset services.'], E2: ['not_applicable', 'No crypto-asset services.'], E3: ['not_applicable', 'No crypto-asset services.'] }),
    impacts: { obligationIds: [10], controlIds: [2], policyIds: [2], riskIds: [2] },
    actions: [{ id: 1, title: 'Configure Travel Rule exception queue for EU counterparties', ownerId: 3, dueAt: ago(200), status: 'done' }],
    makerId: 5, approverId: 2,
    history: [
      { at: ago(330), actorName: 'Grace Ibekwe', action: 'Submitted for approval', note: null },
      { at: ago(328), actorName: 'Daniel Okafor', action: 'Approved', note: null },
    ],
  },
]

/** Cross-border comparison of how requirements differ, and whether an existing control can be reused. */
export const COMPARISONS = [
  {
    topic: 'Suspicious activity reporting', controlId: 3,
    rows: [
      { code: 'GB', requirement: 'SAR to the NCA as soon as practicable; consent regime for proceeding with suspected transactions.', reuse: 'reused' },
      { code: 'NG', requirement: 'STR to the NFIU within 24 hours of suspicion.', reuse: 'local_variant' },
      { code: 'AE', requirement: 'STR to the UAE FIU through goAML without delay.', reuse: 'local_variant' },
      { code: 'EU', requirement: 'STR to FIU Ireland and Revenue as soon as practicable.', reuse: 'reused' },
    ],
  },
  {
    topic: 'Personal data breach notification', controlId: 16,
    rows: [
      { code: 'GB', requirement: 'Notify the ICO within 72 hours where there is a risk to individuals.', reuse: 'reused' },
      { code: 'NG', requirement: 'Notify the NDPC within 72 hours of awareness.', reuse: 'reused' },
      { code: 'AE', requirement: 'Notify the UAE Data Office without delay.', reuse: 'local_variant' },
      { code: 'EU', requirement: 'Notify the Data Protection Commission within 72 hours.', reuse: 'reused' },
    ],
  },
  {
    topic: 'Sanctions screening lists', controlId: 2,
    rows: [
      { code: 'GB', requirement: 'UK sanctions list (OFSI) and UN lists.', reuse: 'reused' },
      { code: 'NG', requirement: 'UN lists and the Nigeria Sanctions Committee list.', reuse: 'local_variant' },
      { code: 'AE', requirement: 'UN lists and the UAE Local Terrorist List.', reuse: 'local_variant' },
      { code: 'EU', requirement: 'EU consolidated list and UN lists.', reuse: 'local_variant' },
    ],
  },
  {
    topic: 'Virtual-asset Travel Rule', controlId: null,
    rows: [
      { code: 'GB', requirement: 'Applies to UK cryptoasset businesses.', reuse: 'not_applicable' },
      { code: 'NG', requirement: 'FATF-aligned expectations for SEC-registered platforms.', reuse: 'gap' },
      { code: 'AE', requirement: 'Applies to licensed virtual asset providers.', reuse: 'not_applicable' },
      { code: 'EU', requirement: 'Every crypto-asset transfer, with checks for self-hosted wallets.', reuse: 'gap' },
    ],
  },
]

// ── Transaction monitoring ─────────────────────────────────────────────────────────────────

export interface TmChannel { key: string, name: string, source: string, mode: 'real-time' | 'near-real-time' | 'batch', entityIds: string[], volume24h: number, alerts24h: number, latency: string, status: 'simulated' | 'planned' }

export const TM_CHANNELS: TmChannel[] = [
  { key: 'bank', name: 'Bank transfers', source: 'Core banking (Kafka stream)', mode: 'real-time', entityIds: ['E1', 'E2', 'E5'], volume24h: 48_210, alerts24h: 6, latency: 'Under 5 seconds', status: 'simulated' },
  { key: 'card', name: 'Cards and merchants', source: 'Card processor feed', mode: 'near-real-time', entityIds: ['E1', 'E5'], volume24h: 211_400, alerts24h: 3, latency: 'Under 1 minute', status: 'simulated' },
  { key: 'mobile_money', name: 'Mobile money', source: 'Mobile money platform API', mode: 'near-real-time', entityIds: ['E2'], volume24h: 389_050, alerts24h: 9, latency: 'Under 1 minute', status: 'simulated' },
  { key: 'remittance', name: 'Remittance', source: 'Remittance platform (hourly batch)', mode: 'batch', entityIds: ['E3'], volume24h: 12_880, alerts24h: 2, latency: 'Hourly', status: 'simulated' },
  { key: 'wallet', name: 'E-wallets', source: 'Wallet ledger events', mode: 'real-time', entityIds: ['E2', 'E5'], volume24h: 64_300, alerts24h: 1, latency: 'Under 5 seconds', status: 'simulated' },
  { key: 'crypto', name: 'Crypto transfers', source: 'Blockchain analytics provider (to be selected)', mode: 'near-real-time', entityIds: ['E4'], volume24h: 3_420, alerts24h: 4, latency: 'Depends on provider', status: 'planned' },
]

export interface TmScenario {
  id: string, name: string, kind: 'rule' | 'behavioural', channels: string[], threshold: string, status: 'active' | 'testing'
  alerts30d: number, falsePositiveRate: number, caseConversion: number, lastTunedAt: string, ownerId: number, linkedRule: string | null
}

export const TM_SCENARIOS: TmScenario[] = [
  { id: 'TM-01', name: 'High-value single transfer', kind: 'rule', channels: ['bank', 'remittance'], threshold: 'At or above £10,000 equivalent', status: 'active', alerts30d: 142, falsePositiveRate: 38, caseConversion: 9, lastTunedAt: ago(45), ownerId: 3, linkedRule: 'CCM-002' },
  { id: 'TM-02', name: 'Velocity: rapid outbound transfers', kind: 'rule', channels: ['bank', 'mobile_money', 'wallet'], threshold: 'More than 10 outbound transfers in 1 hour', status: 'active', alerts30d: 88, falsePositiveRate: 22, caseConversion: 14, lastTunedAt: ago(30), ownerId: 3, linkedRule: null },
  { id: 'TM-03', name: 'Structuring below reporting threshold', kind: 'rule', channels: ['bank', 'mobile_money'], threshold: '3 or more deposits within 10% below threshold in 7 days', status: 'active', alerts30d: 31, falsePositiveRate: 18, caseConversion: 26, lastTunedAt: ago(60), ownerId: 3, linkedRule: null },
  { id: 'TM-04', name: 'Pass-through activity', kind: 'rule', channels: ['bank', 'wallet'], threshold: 'Over 80% of inbound funds sent out within 24 hours', status: 'active', alerts30d: 47, falsePositiveRate: 25, caseConversion: 19, lastTunedAt: ago(30), ownerId: 3, linkedRule: null },
  { id: 'TM-05', name: 'Dormant account reactivation', kind: 'rule', channels: ['bank'], threshold: 'No activity for 12 months, then over £5,000 in 7 days', status: 'active', alerts30d: 12, falsePositiveRate: 33, caseConversion: 17, lastTunedAt: ago(90), ownerId: 3, linkedRule: null },
  { id: 'TM-06', name: 'Money mule indicators', kind: 'behavioural', channels: ['bank', 'mobile_money'], threshold: 'Many unrelated payers followed by rapid cash-out', status: 'active', alerts30d: 26, falsePositiveRate: 15, caseConversion: 31, lastTunedAt: ago(20), ownerId: 3, linkedRule: null },
  { id: 'TM-07', name: 'High-risk geography counterparty', kind: 'rule', channels: ['bank', 'remittance', 'card'], threshold: 'Counterparty in a high-risk jurisdiction', status: 'active', alerts30d: 64, falsePositiveRate: 41, caseConversion: 7, lastTunedAt: ago(75), ownerId: 3, linkedRule: null },
  { id: 'TM-08', name: 'Behavioural change in volume', kind: 'behavioural', channels: ['bank', 'card', 'mobile_money'], threshold: 'Volume more than 3 standard deviations above 90-day baseline', status: 'active', alerts30d: 39, falsePositiveRate: 28, caseConversion: 15, lastTunedAt: ago(30), ownerId: 3, linkedRule: 'CCM-011' },
  { id: 'TM-09', name: 'Mobile money agent cash-out cluster', kind: 'behavioural', channels: ['mobile_money'], threshold: 'Same agent cashes out for 15 or more new accounts in 24 hours', status: 'testing', alerts30d: 9, falsePositiveRate: 44, caseConversion: 11, lastTunedAt: ago(7), ownerId: 3, linkedRule: null },
  { id: 'TM-10', name: 'Round-amount remittance pattern', kind: 'rule', channels: ['remittance'], threshold: '5 or more round-amount remittances to the same beneficiary in 7 days', status: 'active', alerts30d: 17, falsePositiveRate: 35, caseConversion: 12, lastTunedAt: ago(50), ownerId: 3, linkedRule: null },
  { id: 'TM-11', name: 'Crypto off-ramp after high-risk exposure', kind: 'rule', channels: ['crypto'], threshold: 'Fiat withdrawal within 48 hours of inbound funds with high-risk exposure', status: 'testing', alerts30d: 6, falsePositiveRate: 17, caseConversion: 33, lastTunedAt: ago(10), ownerId: 3, linkedRule: null },
]

export interface TmAlert {
  id: number, ref: string, scenarioId: string, channel: string, entityId: string, customerId: number
  amount: number, currency: string, amountGbp: number, at: string, score: number
  status: 'new' | 'in_review' | 'escalated' | 'closed', assigneeId: number | null, summary: string
  baseline: string, observed: string
  linked: { at: string, description: string, amount: number, currency: string }[]
  outcome: string | null, caseRef: string | null
}

export const TM_ALERTS: TmAlert[] = [
  {
    id: 1, ref: 'TMA-2041', scenarioId: 'TM-06', channel: 'mobile_money', entityId: 'E2', customerId: 6, amount: 4_850_000, currency: 'NGN', amountGbp: 2_400, at: ago(0, 3), score: 78,
    status: 'new', assigneeId: null, summary: '23 unrelated senders paid into the wallet in 6 hours, then 92% was cashed out at two agents.',
    baseline: '3 inbound payments a week, average NGN 40,000', observed: '23 inbound payments in 6 hours, NGN 4.85m',
    linked: [{ at: ago(0, 9), description: '23 inbound transfers from 23 different wallets', amount: 4_850_000, currency: 'NGN' }, { at: ago(0, 4), description: 'Cash-out at agent AG-7712 (Ikeja)', amount: 2_900_000, currency: 'NGN' }, { at: ago(0, 3), description: 'Cash-out at agent AG-7790 (Yaba)', amount: 1_560_000, currency: 'NGN' }],
    outcome: null, caseRef: null,
  },
  {
    id: 2, ref: 'TMA-2040', scenarioId: 'TM-03', channel: 'bank', entityId: 'E2', customerId: 17, amount: 14_700_000, currency: 'NGN', amountGbp: 7_300, at: ago(0, 7), score: 66,
    status: 'in_review', assigneeId: 4, summary: 'Five cash deposits each just under the internal reporting threshold within six days.',
    baseline: 'Salary credit monthly, no cash deposits', observed: '5 cash deposits of NGN 2.9m to 2.98m in 6 days',
    linked: [{ at: ago(6), description: 'Cash deposit, Lekki branch', amount: 2_950_000, currency: 'NGN' }, { at: ago(4), description: 'Cash deposit, Victoria Island branch', amount: 2_980_000, currency: 'NGN' }, { at: ago(1), description: 'Cash deposit, Ikoyi branch', amount: 2_900_000, currency: 'NGN' }],
    outcome: null, caseRef: null,
  },
  {
    id: 3, ref: 'TMA-2039', scenarioId: 'TM-04', channel: 'bank', entityId: 'E1', customerId: 14, amount: 186_000, currency: 'GBP', amountGbp: 186_000, at: ago(0, 11), score: 81,
    status: 'new', assigneeId: null, summary: 'GBP 186,000 received from three new payers and 94% sent abroad within 20 hours.',
    baseline: 'Monthly turnover around GBP 30,000', observed: 'GBP 186,000 in and GBP 175,000 out within 20 hours',
    linked: [{ at: ago(1), description: 'Inbound from three new UK payers', amount: 186_000, currency: 'GBP' }, { at: ago(0, 12), description: 'Outbound to Dubai trading account', amount: 175_000, currency: 'GBP' }],
    outcome: null, caseRef: null,
  },
  {
    id: 4, ref: 'TMA-2038', scenarioId: 'TM-10', channel: 'remittance', entityId: 'E3', customerId: 12, amount: 50_000, currency: 'AED', amountGbp: 10_700, at: ago(1, 2), score: 54,
    status: 'new', assigneeId: null, summary: 'Five remittances of exactly AED 10,000 to the same beneficiary in Lagos within a week.',
    baseline: 'One remittance a month to family', observed: '5 x AED 10,000 to one beneficiary in 7 days',
    linked: [{ at: ago(7), description: 'Remittance to beneficiary B-55102 (Lagos)', amount: 10_000, currency: 'AED' }, { at: ago(4), description: 'Remittance to beneficiary B-55102 (Lagos)', amount: 10_000, currency: 'AED' }, { at: ago(1), description: 'Remittance to beneficiary B-55102 (Lagos)', amount: 10_000, currency: 'AED' }],
    outcome: null, caseRef: null,
  },
  {
    id: 5, ref: 'TMA-2037', scenarioId: 'TM-08', channel: 'card', entityId: 'E1', customerId: 9, amount: 64_200, currency: 'GBP', amountGbp: 64_200, at: ago(1, 6), score: 47,
    status: 'in_review', assigneeId: 4, summary: 'Card takings 4.1 standard deviations above the 90-day average for this bakery.',
    baseline: 'GBP 15,600 card takings a week', observed: 'GBP 64,200 card takings this week',
    linked: [{ at: ago(2), description: 'Card settlements, 412 transactions', amount: 64_200, currency: 'GBP' }],
    outcome: null, caseRef: null,
  },
  {
    id: 6, ref: 'TMA-2036', scenarioId: 'TM-11', channel: 'crypto', entityId: 'E4', customerId: 17, amount: 18_400, currency: 'USD', amountGbp: 14_500, at: ago(1, 9), score: 74,
    status: 'new', assigneeId: null, summary: 'USDT received with indirect mixer exposure, converted and withdrawn to a bank account within 36 hours.',
    baseline: 'Small monthly purchases of BTC', observed: 'USD 18,400 inbound USDT, fiat withdrawal 36 hours later',
    linked: [{ at: ago(3), description: 'Inbound 18,400 USDT (Tron) from wallet with 24% mixer exposure', amount: 18_400, currency: 'USD' }, { at: ago(1, 12), description: 'Withdrawal to Nigerian bank account', amount: 18_100, currency: 'USD' }],
    outcome: null, caseRef: null,
  },
  {
    id: 7, ref: 'TMA-2035', scenarioId: 'TM-07', channel: 'bank', entityId: 'E1', customerId: 7, amount: 72_000, currency: 'GBP', amountGbp: 72_000, at: ago(2), score: 69,
    status: 'escalated', assigneeId: 3, summary: 'Inbound payments from a counterparty in a high-risk jurisdiction (VG).',
    baseline: 'Salary and card spending only', observed: 'First payments from the British Virgin Islands',
    linked: [{ at: ago(2), description: 'Inbound from British Virgin Islands counterparty', amount: 72_000, currency: 'GBP' }],
    outcome: 'Escalated to case AML-2193', caseRef: 'AML-2193',
  },
  {
    id: 8, ref: 'TMA-2034', scenarioId: 'TM-02', channel: 'wallet', entityId: 'E5', customerId: 11, amount: 9_800, currency: 'EUR', amountGbp: 8_400, at: ago(3), score: 31,
    status: 'closed', assigneeId: 4, summary: 'Fourteen outbound wallet transfers in one hour.',
    baseline: 'Payroll batch monthly', observed: '14 transfers in 1 hour',
    linked: [{ at: ago(3), description: '14 outbound wallet transfers to staff', amount: 9_800, currency: 'EUR' }],
    outcome: 'False positive: monthly contractor payroll run, confirmed with the customer.', caseRef: null,
  },
]

// ── Digital assets ─────────────────────────────────────────────────────────────────────────

export type ExposureCategory = 'sanctioned' | 'mixer' | 'darknet' | 'ransomware' | 'scam' | 'stolen_funds' | 'high_risk_service' | 'gambling' | 'exchange'

export interface Wallet {
  id: number, address: string, chain: string, asset: string, customerId: number | null, label: string
  riskScore: number, status: 'clear' | 'review' | 'blocked'
  exposures: { category: ExposureCategory, percent: number, kind: 'direct' | 'indirect' }[]
  screenedAt: string, illustrative: boolean
}

export const WALLETS: Wallet[] = [
  { id: 1, address: 'TXk7pQ2vR9mWb4nE3sLh8cJ6dF1gY5uA2z', chain: 'Tron', asset: 'USDT', customerId: 17, label: 'Inbound counterparty to Femi Adebayo', riskScore: 82, status: 'review', exposures: [{ category: 'mixer', percent: 24, kind: 'indirect' }, { category: 'exchange', percent: 61, kind: 'direct' }, { category: 'high_risk_service', percent: 15, kind: 'indirect' }], screenedAt: ago(3), illustrative: true },
  { id: 2, address: 'bc1q9h6tq0x4w3l2y8k7d5f2r1m0p8s6v4c3n2j7e', chain: 'Bitcoin', asset: 'BTC', customerId: 6, label: 'Withdrawal destination, Nneka Okoro-Bassey', riskScore: 12, status: 'clear', exposures: [{ category: 'exchange', percent: 97, kind: 'direct' }], screenedAt: ago(5), illustrative: true },
  { id: 3, address: '0x7a3F19c2B8e04d6A5f1E92b3C7d84A0e6B1c9D2f', chain: 'Ethereum', asset: 'USDC', customerId: null, label: 'Counterparty wallet (unhosted)', riskScore: 96, status: 'blocked', exposures: [{ category: 'sanctioned', percent: 41, kind: 'direct' }, { category: 'ransomware', percent: 18, kind: 'indirect' }], screenedAt: ago(1), illustrative: true },
  { id: 4, address: '0x2c94E1b07F3a8D5c6B19e0A4f7D2c3B8a5E6f901', chain: 'Polygon', asset: 'USDT', customerId: 1, label: 'Treasury wallet, Hadejia Trading Ltd', riskScore: 38, status: 'review', exposures: [{ category: 'exchange', percent: 72, kind: 'direct' }, { category: 'gambling', percent: 9, kind: 'indirect' }, { category: 'scam', percent: 4, kind: 'indirect' }], screenedAt: ago(2), illustrative: true },
  { id: 5, address: 'bc1qm4x8r2c7v0n5t3w9k6j1h8g4f2d0s7a5p3l9q', chain: 'Bitcoin', asset: 'BTC', customerId: null, label: 'Counterparty wallet (darknet market cluster)', riskScore: 91, status: 'blocked', exposures: [{ category: 'darknet', percent: 63, kind: 'direct' }, { category: 'stolen_funds', percent: 7, kind: 'indirect' }], screenedAt: ago(4), illustrative: true },
]

export interface CryptoTransfer {
  id: number, hash: string, chain: string, asset: string, amount: number, valueUsd: number, direction: 'in' | 'out', at: string
  customerId: number, entityId: string, counterpartyWalletId: number | null, counterpartyType: 'vasp' | 'unhosted' | 'unknown'
  crossChain: string | null, riskScore: number, status: 'open' | 'escalated' | 'cleared'
  path: { label: string, category: ExposureCategory | 'customer' | 'unknown' }[]
  caseRef: string | null
}

export const TRANSFERS: CryptoTransfer[] = [
  { id: 1, hash: '9f2c41e07a6b3d58c1e94f20a7b6c3d5e8f1a2b4c6d8e0f1a3b5c7d9e1f2a4b6', chain: 'Tron', asset: 'USDT', amount: 18_400, valueUsd: 18_400, direction: 'in', at: ago(3), customerId: 17, entityId: 'E4', counterpartyWalletId: 1, counterpartyType: 'unhosted', crossChain: null, riskScore: 82, status: 'open', path: [{ label: 'Mixing service', category: 'mixer' }, { label: 'Intermediate wallet', category: 'unknown' }, { label: 'TXk7…A2z', category: 'unknown' }, { label: 'Femi Adebayo deposit address', category: 'customer' }], caseRef: null },
  { id: 2, hash: '0x5d8e2b7f1a4c9036e2d8b5a1f7c4e9b0d3a6f2c8e1b5d9a4f7c0e3b6d2a8f1c5', chain: 'Ethereum', asset: 'USDC', amount: 52_000, valueUsd: 52_000, direction: 'out', at: ago(1), customerId: 1, entityId: 'E4', counterpartyWalletId: 3, counterpartyType: 'unhosted', crossChain: null, riskScore: 96, status: 'open', path: [{ label: 'Hadejia Trading Ltd withdrawal', category: 'customer' }, { label: '0x7a3F…D2f', category: 'unknown' }, { label: 'Sanctioned entity cluster', category: 'sanctioned' }], caseRef: null },
  { id: 3, hash: '0x8b1d4f7a2c5e9036b8d1f4a7c2e5b9d0a3f6c8e1b4d7a0f3c6e9b2d5a8f1c4e7', chain: 'Polygon', asset: 'USDT', amount: 9_750, valueUsd: 9_750, direction: 'in', at: ago(2), customerId: 1, entityId: 'E4', counterpartyWalletId: 4, counterpartyType: 'vasp', crossChain: 'Bridged from Ethereum', riskScore: 38, status: 'open', path: [{ label: 'Exchange hot wallet', category: 'exchange' }, { label: 'Cross-chain bridge', category: 'unknown' }, { label: 'Hadejia Trading Ltd deposit address', category: 'customer' }], caseRef: null },
  { id: 4, hash: 'c3a9e17f4b2d8065a1c7e3f9b5d2a8c4e0f6b1d3a9c5e7f2b4d8a0c6e1f3b5d7', chain: 'Bitcoin', asset: 'BTC', amount: 0.42, valueUsd: 27_300, direction: 'out', at: ago(4), customerId: 6, entityId: 'E4', counterpartyWalletId: 2, counterpartyType: 'vasp', crossChain: null, riskScore: 12, status: 'cleared', path: [{ label: 'Nneka Okoro-Bassey withdrawal', category: 'customer' }, { label: 'Licensed exchange', category: 'exchange' }], caseRef: null },
]

export interface TravelRuleItem {
  id: number, transferId: number | null, direction: 'in' | 'out', asset: string, amount: number, valueUsd: number, at: string
  counterpartyVasp: string | null, counterpartyJurisdiction: string | null, vaspDueDiligence: 'complete' | 'pending' | 'not_started' | 'n/a'
  originatorComplete: boolean, beneficiaryComplete: boolean
  issue: string, status: 'exception' | 'pending_info' | 'resolved' | 'rejected', note: string | null
}

export const TRAVEL_RULE: TravelRuleItem[] = [
  { id: 1, transferId: 1, direction: 'in', asset: 'USDT', amount: 18_400, valueUsd: 18_400, at: ago(3), counterpartyVasp: null, counterpartyJurisdiction: null, vaspDueDiligence: 'n/a', originatorComplete: false, beneficiaryComplete: true, issue: 'Unhosted wallet: originator name and wallet ownership not provided', status: 'exception', note: null },
  { id: 2, transferId: null, direction: 'out', asset: 'BTC', amount: 0.15, valueUsd: 9_800, at: ago(0, 6), counterpartyVasp: 'Harbour Digital Exchange', counterpartyJurisdiction: 'AE', vaspDueDiligence: 'pending', originatorComplete: true, beneficiaryComplete: false, issue: 'Beneficiary name missing; counterparty VASP due diligence not complete', status: 'exception', note: null },
  { id: 3, transferId: null, direction: 'in', asset: 'USDT', amount: 4_200, valueUsd: 4_200, at: ago(1), counterpartyVasp: 'Obudu Digital Exchange', counterpartyJurisdiction: 'NG', vaspDueDiligence: 'complete', originatorComplete: true, beneficiaryComplete: true, issue: 'Name mismatch between originator and account holder', status: 'pending_info', note: 'Requested corrected originator details from Obudu Digital Exchange.' },
  { id: 4, transferId: 4, direction: 'out', asset: 'BTC', amount: 0.42, valueUsd: 27_300, at: ago(4), counterpartyVasp: 'Eko Coin Exchange', counterpartyJurisdiction: 'NG', vaspDueDiligence: 'complete', originatorComplete: true, beneficiaryComplete: true, issue: 'Counterparty confirmation received late', status: 'resolved', note: 'Information complete; transfer released.' },
]

// ── Delivery governance (internal) ─────────────────────────────────────────────────────────

export const GATES = [
  { id: 0, name: 'Programme authority', decision: 'Approve mandate, ownership, budget, decision rights and commercial objectives.', status: 'pending_decision' as const },
  { id: 1, name: 'Product discovery', decision: 'Validate priority customers, journeys, requirements, pricing and design partners.', status: 'not_started' as const },
  { id: 2, name: 'Architecture and trust', decision: 'Approve target architecture, common data model, security, privacy, AI and regulatory-content governance.', status: 'not_started' as const },
  { id: 3, name: 'Mobilisation', decision: 'Appoint team and suppliers; prepare environments, providers, contracts and delivery backlog.', status: 'not_started' as const },
  { id: 4, name: 'Build authorisation', decision: 'Re-date and approve the detailed sprint plan and acceptance baseline.', status: 'not_started' as const },
]

/** The five commercial MVP journeys (Version 1.2, section 8) and where each is shown in the demo. */
export const MVP_JOURNEYS = [
  { journey: 'Regulatory change to control', outcome: 'Identify change, assess applicability, update the obligation and map required controls and actions.', where: 'Regulatory change' },
  { journey: 'Customer onboarding to monitoring', outcome: 'KYC or KYB, screening and risk scoring, trigger EDD and schedule ongoing monitoring.', where: 'Customers and screening' },
  { journey: 'Control failure to closure', outcome: 'Detect failure, open an issue, remediate, validate evidence and conclude assurance.', where: 'Alerts and Controls' },
  { journey: 'Third party to cross-border exposure', outcome: 'Supplier identity, services, jurisdictions, sanctions, cyber, regulatory and contractual risk.', where: 'Third parties' },
  { journey: 'Executive assurance view', outcome: 'Entity, jurisdiction, risk, control, financial-crime and assurance exposure in one view.', where: 'Overview' },
]
