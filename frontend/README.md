# OysterChecks Integrated Platform (demo)

Nuxt 4 front end of *OysterChecks Integrated Platform Documentation v1.2* (GRC assurance, financial
crime, Perimeter Intelligence and Digital Assets Intelligence), packaged as a **static demo**: no
server and no database. Every screen works from hardcoded demo data, so the
generated site can be hosted on any static host (Netlify, Vercel, GitHub Pages, S3, IIS, a USB stick).

## Quick start

```bash
cd frontend
npm install          # if npm 10.9.x fails with "Cannot read properties of null (reading 'edgesOut')",
                     # use: npx npm@11 install   (npm bug, not a project issue)
npm run dev          # http://localhost:3000
```

Sign in with any demo account, password `OysterDemo!2026`: `cro@`, `cco@`, `mlro@`, `analyst@`,
`compliance@`, `audit@`, `ciso@`, `hr@`, `retail.manager@`, `payments.manager@`, `board@`, `admin@`,
all at `@oysterchecks.demo`. Each role lands on its own screen (§1.3).

| Command | |
|---|---|
| `npm run dev` | Dev server |
| `npm run generate` | Static site in `.output/public` (`npm run build` does the same) |
| `npm run preview` | Serve the generated site locally |
| `npm test` | Unit tests for the domain logic (risk, SLA, AML, supplier tiering, RBAC) |
| `npm run typecheck` | Type-check |

## Deploying the static site

`npm run generate`, then upload the **contents** of `.output/public` (including the hidden
`.htaccess` file). Compiled scripts and styles are in `_assets/`.

- **Apache / cPanel:** the included `.htaccess` (from `public/.htaccess`) sends every page URL to
  the app, caches `_assets/` for a year and never caches the HTML. It needs `mod_rewrite`, which
  shared hosts enable by default.
- **Netlify, Vercel, GitHub Pages:** nothing extra; they serve `200.html` / `404.html` automatically.
- **Nginx:** `try_files $uri $uri/ /200.html;`
- **IIS:** a URL Rewrite rule that sends non-file requests to `/200.html`.

**Sub-folder** (e.g. `example.com/oysterchecks/`): build with the folder as the base path, then
uncomment `RewriteBase` in `.htaccess` and point `ErrorDocument` at the folder.

```powershell
$env:NUXT_APP_BASE_URL = '/oysterchecks/'; npm run generate; Remove-Item Env:NUXT_APP_BASE_URL
```

In Git Bash, prefix the command with `MSYS_NO_PATHCONV=1`
(`MSYS_NO_PATHCONV=1 NUXT_APP_BASE_URL=/oysterchecks/ npm run generate`); otherwise Git Bash
rewrites `/oysterchecks/` into a Windows path and the build produces "Redirecting..." pages.

## How the demo works

| Part | Where | What it does |
|---|---|---|
| Version 1.2 data and API | `app/demo/extended.ts`, `app/demo/api-v12.ts` | Legal entities, jurisdiction packs, regulatory sources and changes, monitoring channels, scenarios and alerts, wallets, transfers, Travel Rule records, delivery gates, and the rules that act on them. |
| Demo data | `app/demo/fixtures.json` | Every API response the UI reads, captured once per role from the full platform, so role restrictions, business-unit scoping and PII masking are real. Dates are moved forward on load, so "overdue" and "due in 3 days" always read relative to today. |
| Demo API | `app/demo/api.ts` | Answers every `/api/...` call in the browser and applies the business rules: RBAC, four-eyes alert closure, MLRO-only SAR submission, policy approver ≠ owner, incident closure gates, control effectiveness from test results, residual risk recalculation, KRI breach alerts, hash-chained audit entries. |
| Changes | `app/demo/state.ts` | What you do in the demo is kept in the browser (localStorage) and shared between tabs. The sign-in is per tab, so two tabs can be two different people, for example to show four-eyes sign-off. **Reset demo data** is in the header and on the sign-in page. |
| Wiring | `app/plugins/00.demo-api.ts` | Routes `$fetch('/api/...')` to the demo API, turns export and evidence links into downloads, and replays changes from other tabs as live updates. |

Uploaded evidence files stay in the memory of the tab that uploaded them; other tabs see the file
name and SHA-256 fingerprint.

## What Version 1.2 added

Version 1.2 is a five-page integration baseline placed in front of the unchanged v1.0 document;
where they conflict, v1.2 wins.

| v1.2 | Implementation |
|---|---|
| §2 Product structure | Five layers: core, Financial Crime Intelligence, Enterprise GRC and Assurance, Perimeter Intelligence, Digital Assets Intelligence. The demo client is a group with five legal entities (UK, Nigeria x2, UAE, Ireland), in `app/demo/extended.ts`. |
| §3 Traceability | Source → obligation → legal entity → risk → control → monitoring rule → alert → action → evidence → assurance conclusion, shown in the obligation drawer (`ComplianceTraceChain`, `/api/trace/obligation/:id`). |
| §4 Perimeter Intelligence | *Coverage and sources*: jurisdiction packs with local language, currency, time zone and data residency; source inventory with provenance and fingerprints; cross-border comparison with control reuse; content freshness and quality. *Regulatory change*: capture, validation of automatically extracted requirements, applicability per legal entity, impact mapping, actions and **maker-checker approval** (the preparer cannot approve). |
| §5 Transaction monitoring and digital assets | *Transaction monitoring*: bank, card, mobile money, remittance, wallet and crypto channels; 11 scenarios with performance and audited tuning; alert queue escalating into the shared case model. *Digital assets*: wallet screening, exposure, tracing, Travel Rule exceptions (release blocked until counterparty VASP due diligence). Both carry a **planned capability** notice, as §5 requires. |
| §6 Navigation | The 14 customer sections. Cases have their own section. The Build Programme view is internal: it is under Administration and only Admin and Board (programme governance) can open it. |
| §8 MVP journeys | Executive view: *Group exposure by legal entity* on the Overview. The five journeys are listed on the programme view. |
| §9 Delivery status | Pre-initiation. Governance gates 0 to 4 must be approved in order; the six phases are provisional and locked until Gate 4. |

Permissions added: `perimeter:read/write/approve`, `tm:read/write`, `crypto:read/write`. The MLRO
now starts on *Cases* and the AML Analyst on *Transaction monitoring*.

## How the v1.0 content maps to the specification

| Spec | Implementation |
|---|---|
| §2.3 RBAC, immutable audit log | RBAC matrix in `shared/utils/rbac.ts`, checked on every API call; BU managers are row-scoped to their unit; Admin sees PII masked. Every action adds a hash-chained audit entry; integrity check under *Audit log*. |
| §4.4, §5 CCM | 12 rules with SLA, acknowledgement deadlines and escalation paths. Rule changes require a reason and are audited. |
| §4.4 step 7 alert closure | Request closure with evidence → sign-off by a *different* user with `alerts:close` (four-eyes). |
| §4.3 ERM | Inherent = impact × likelihood; residual derived from mapped controls' **tested** effectiveness (`shared/utils/risk.ts`). KRIs above their red limit raise alerts. |
| §4.5 AML | Transparent factor-based scoring (every point explained), screening decisions, EDD with senior approval, SAR decision → NCA submission (MLRO only). |
| §4.6 Three lines | Activities per line, CSA portal, coverage map flagging gaps and over-assurance. CSA results are shown against, never override, test evidence. |
| §4.2, §4.7, §4.8, Module J | Policy lifecycle with approver ≠ owner and acknowledgement tracking; supplier tiering and review cadence; incidents with RCA, linking, regulator notification deadlines and gated closure; ESG metrics with computed status. |
| §6 UI | Light and dark themes (toggle in the header, defaults to the OS setting) built from design tokens in `app/assets/css/main.css`. IBM Plex Sans and Plex Mono. |

## Deviations from the document (deliberate)

- **CCM rule numbers.** §4.5 cites CCM-005/006/008 for EDD, SAR and KYC refresh; the §5 table (used here) numbers them 004/003/005.
- **SAR clock.** CCM-003 measures 7 days from the SAR *decision* (§4.5), not from case opening (§5 wording).
- **Programme.** 6 phases over 26 weeks (the "27 weeks" heading and "7-step stepper" contradict the doc's own table).
- **Visual design.** v1.0 and v1.2 name the dark-only "Obsidian Command" look (neon accents, three typefaces) as the interface standard. At the product owner's request it was replaced with a conventional light/dark enterprise style; its muted text colour also failed WCAG AA contrast. New v1.2 views use the same components, so there is still one visual identity.
- **Regulatory content** in the Perimeter packs names real instruments, but summaries are simplified and illustrative.
- **Digital assets provider.** No blockchain analytics provider is selected, so wallet screening returns a deterministic illustrative result.
- **Extra roles.** *AML Analyst* and *Compliance Officer* added. The spec's screens name them as users but the role table omits them.
- **Low-tier suppliers** reviewed every 36 months (spec is silent).
- **Risk distribution** is a ranked bar list rather than a donut, which makes categories easier to compare accurately.

## Demo limits

- No MFA, password checks or account lockout: sign-in only checks the demo password.
- Control monitoring does not re-evaluate rules against new data; *Run now* records a run.
- Everything resets when browser storage is cleared.

The `backend/` folder at the repository root is reserved for the production service (database,
CCM engine, connectors, SSO). The full-stack version of this app is in the git history before the
"static demo" commit.
