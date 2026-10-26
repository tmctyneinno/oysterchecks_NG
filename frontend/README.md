# OysterChecks GRC & Assurance Platform

Full-stack Nuxt 4 implementation of *OysterChecks GRC & Assurance Platform Documentation v1.0*.
One application serves the UI and the API (Nitro), backed by SQLite through Drizzle ORM.

## Quick start

```bash
cd frontend
npm install          # if npm 10.9.x fails with "Cannot read properties of null (reading 'edgesOut')",
                     # use: npx npm@11 install   (npm bug, not a project issue)
cp .env.example .env # then set NUXT_SESSION_PASSWORD; for local dev you may set NUXT_MFA_ENFORCED=false
npm run dev          # http://localhost:3000
```

On first boot the database is migrated and seeded. Sign in with any demo account (password
`OysterDemo!2026`): `cro@`, `cco@`, `mlro@`, `analyst@`, `compliance@`, `audit@`, `ciso@`, `hr@`,
`retail.manager@`, `payments.manager@`, `board@`, `admin@`, all at `@oysterchecks.demo`.
Each role lands on its own screen (§1.3). With MFA enforced, the first sign-in walks through
authenticator-app enrolment.

| Command | |
|---|---|
| `npm run dev` | Dev server |
| `npm test` | Unit tests (domain logic + every CCM evaluator against the seeded data) |
| `npm run typecheck` | Type-check app and server |
| `npm run db:generate` | Generate a migration after editing `server/database/schema.ts` |
| `npm run db:reset` | Delete the local database; it is re-seeded on next start |

## How it maps to the specification

| Spec | Implementation |
|---|---|
| §2.3 MFA, RBAC, immutable audit log | TOTP MFA (`server/utils/crypto.ts`), lockout after 5 failures, sealed-cookie sessions. RBAC matrix in `shared/utils/rbac.ts`, enforced on **every** API call (`requirePermission`); BU managers are row-scoped to their unit; Admin sees PII masked. Audit log is hash-chained and protected by SQLite triggers against UPDATE/DELETE; integrity check under *Audit Log*. |
| §4.4, §5 CCM | `server/ccm/evaluators.ts`: 12 rules evaluated every minute against source tables. Breaches raise de-duplicated alerts with SLA and acknowledgement deadlines; `runEscalation` walks the escalation path on SLA breach and notifies the Board Audit Committee for critical overdue items. Rule changes require a reason and are audited. |
| §4.4 step 7 alert closure | Request closure with evidence → sign-off by a *different* user with `alerts:close` (four-eyes). |
| §4.3 ERM | Inherent = impact × likelihood; residual derived from mapped controls' **tested** effectiveness (`shared/utils/risk.ts`). KRIs with amber/red thresholds raise alerts. |
| §4.5 AML | Transparent factor-based scoring (every point explained), screening decisions, EDD with senior approval, SAR decision → NCA submission (MLRO only), CCM-003 7-day clock. |
| §4.6 Three lines | Activities per line, CSA portal, coverage map flagging gaps and over-assurance. CSA results are shown against, never override, test evidence. |
| §4.2, §4.7, §4.8, Module J | Policy lifecycle with approver ≠ owner and acknowledgement tracking; supplier tiering and review cadence; incidents with RCA, linking, regulator notification deadlines and gated closure; ESG metrics with computed status. |
| §6 UI | Light and dark themes (toggle in the header, defaults to the OS setting) built from design tokens in `app/assets/css/main.css`. IBM Plex Sans and Plex Mono. |
| §10.4 real-time | SSE stream (`/api/stream`) filtered per user; UI shows *Stale* and polls every 30s when disconnected. |

## Deviations from the document (deliberate)

- **CCM rule numbers.** §4.5 cites CCM-005/006/008 for EDD, SAR and KYC refresh; the §5 table (used here) numbers them 004/003/005.
- **SAR clock.** CCM-003 measures 7 days from the SAR *decision* (§4.5), not from case opening (§5 wording).
- **Programme.** 6 phases over 26 weeks (the "27 weeks" heading and "7-step stepper" contradict the doc's own table).
- **Visual design.** The spec's dark-only "Obsidian Command" look (neon accents, three typefaces) was replaced with a conventional light/dark enterprise style. Its muted text colour also failed WCAG AA contrast.
- **Extra roles.** *AML Analyst* and *Compliance Officer* added. The spec's screens name them as users but the role table omits them.
- **Low-tier suppliers** reviewed every 36 months (spec is silent).
- **Overdue corrective actions** are monitored alongside the 12 rules (§4.8 step 5) without adding a 13th default rule.
- **Risk distribution** is a ranked bar list rather than a donut, which makes categories easier to compare categories accurately.

## Before production

1. **Real connectors.** `server/utils/simulator.ts` generates demo data. Replace with adapters for Workday, core banking (Kafka), Salesforce, SAP, Graph API and LSEG writing to the same tables; set `NUXT_DEMO_SIMULATOR=false`.
2. **Postgres.** Swap `drizzle-orm/better-sqlite3` for `drizzle-orm/node-postgres` (schema uses portable types). Needed for multi-instance deployment; also move the event bus (`server/utils/bus.ts`) to Redis/Kafka and the CCM scheduler to a single worker.
3. **Email/SMS.** `server/utils/notify.ts` has one seam for an email/SMS provider.
4. **Evidence storage.** Files are content-addressed on local disk; move to object storage with WORM retention.
5. **SSO.** SAML/OIDC with the client IdP (§2.3). `nuxt-auth-utils` supports OIDC providers.
6. **UK bank holidays** in business-day SLAs; **LLM board summary** layered over the deterministic narrative.
7. Penetration test (CREST), DPIA, load testing, and an end-to-end browser test suite.
