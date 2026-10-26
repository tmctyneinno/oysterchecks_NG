CREATE TABLE `access_accounts` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`account` text NOT NULL,
	`system` text NOT NULL,
	`holder` text NOT NULL,
	`privileged` integer NOT NULL,
	`groups` text NOT NULL,
	`last_reviewed_at` integer,
	`reviewed_by_id` integer
);
--> statement-breakpoint
CREATE TABLE `actions` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`title` text NOT NULL,
	`source_type` text NOT NULL,
	`source_id` integer NOT NULL,
	`owner_id` integer NOT NULL,
	`due_at` integer NOT NULL,
	`status` text NOT NULL,
	`completed_at` integer,
	`evidence_note` text
);
--> statement-breakpoint
CREATE UNIQUE INDEX `actions_ref_unique` ON `actions` (`ref`);--> statement-breakpoint
CREATE TABLE `alert_events` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`alert_id` integer NOT NULL,
	`at` integer NOT NULL,
	`actor_id` integer,
	`actor_name` text NOT NULL,
	`type` text NOT NULL,
	`note` text
);
--> statement-breakpoint
CREATE INDEX `alert_events_idx` ON `alert_events` (`alert_id`);--> statement-breakpoint
CREATE TABLE `alerts` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`source` text NOT NULL,
	`rule_id` text,
	`dedupe_key` text,
	`entity_type` text,
	`entity_id` integer,
	`title` text NOT NULL,
	`detail` text,
	`module` text NOT NULL,
	`business_unit` text,
	`severity` text NOT NULL,
	`status` text NOT NULL,
	`assignee_id` integer,
	`owner_user_id` integer,
	`escalation_path` text NOT NULL,
	`escalation_level` integer DEFAULT 0 NOT NULL,
	`sla` text NOT NULL,
	`detected_at` integer NOT NULL,
	`ack_due_at` integer NOT NULL,
	`acknowledged_at` integer,
	`due_at` integer NOT NULL,
	`overdue` integer DEFAULT false NOT NULL,
	`remediation_plan` text,
	`closure_evidence` text,
	`closure_requested_at` integer,
	`closed_at` integer,
	`closed_by_id` integer,
	`false_positive` integer DEFAULT false NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `alerts_ref_unique` ON `alerts` (`ref`);--> statement-breakpoint
CREATE INDEX `alerts_status_idx` ON `alerts` (`status`,`severity`);--> statement-breakpoint
CREATE INDEX `alerts_dedupe_idx` ON `alerts` (`dedupe_key`);--> statement-breakpoint
CREATE TABLE `aml_cases` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`customer_id` integer NOT NULL,
	`transaction_id` integer,
	`alert_id` integer,
	`type` text NOT NULL,
	`score` integer NOT NULL,
	`score_factors` text NOT NULL,
	`status` text NOT NULL,
	`analyst_id` integer,
	`opened_at` integer NOT NULL,
	`sar_decision_at` integer,
	`sar_draft` text,
	`sar_submitted_at` integer,
	`nca_reference` text,
	`outcome` text,
	`closed_at` integer
);
--> statement-breakpoint
CREATE UNIQUE INDEX `aml_cases_ref_unique` ON `aml_cases` (`ref`);--> statement-breakpoint
CREATE TABLE `assurance_activities` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`line` integer NOT NULL,
	`provider` text NOT NULL,
	`title` text NOT NULL,
	`activity_type` text NOT NULL,
	`planned_start` integer NOT NULL,
	`planned_end` integer NOT NULL,
	`status` text NOT NULL,
	`rating` text,
	`owner_id` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `audit_engagements` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`title` text NOT NULL,
	`scope` text,
	`business_unit` text NOT NULL,
	`lead_id` integer NOT NULL,
	`status` text NOT NULL,
	`planned_start` integer NOT NULL,
	`planned_end` integer NOT NULL,
	`rating` text
);
--> statement-breakpoint
CREATE UNIQUE INDEX `audit_engagements_ref_unique` ON `audit_engagements` (`ref`);--> statement-breakpoint
CREATE TABLE `audit_findings` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`engagement_id` integer NOT NULL,
	`title` text NOT NULL,
	`rating` text NOT NULL,
	`owner_id` integer NOT NULL,
	`business_unit` text NOT NULL,
	`management_response` text,
	`due_at` integer NOT NULL,
	`status` text NOT NULL,
	`closed_at` integer,
	`closure_evidence` text
);
--> statement-breakpoint
CREATE UNIQUE INDEX `audit_findings_ref_unique` ON `audit_findings` (`ref`);--> statement-breakpoint
CREATE TABLE `audit_log` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`at` integer NOT NULL,
	`actor_id` integer,
	`actor_name` text NOT NULL,
	`action` text NOT NULL,
	`entity_type` text NOT NULL,
	`entity_id` text,
	`detail` text,
	`ip` text,
	`prev_hash` text NOT NULL,
	`hash` text NOT NULL
);
--> statement-breakpoint
CREATE INDEX `audit_entity_idx` ON `audit_log` (`entity_type`,`entity_id`);--> statement-breakpoint
CREATE TABLE `ccm_rules` (
	`id` text PRIMARY KEY NOT NULL,
	`name` text NOT NULL,
	`description` text NOT NULL,
	`evaluator` text NOT NULL,
	`params` text NOT NULL,
	`severity` text NOT NULL,
	`sla` text NOT NULL,
	`escalation_path` text NOT NULL,
	`module` text NOT NULL,
	`data_source` text NOT NULL,
	`enabled` integer DEFAULT true NOT NULL,
	`state` text DEFAULT 'unknown' NOT NULL,
	`last_run_at` integer,
	`last_breaches` integer DEFAULT 0 NOT NULL,
	`last_warnings` integer DEFAULT 0 NOT NULL
);
--> statement-breakpoint
CREATE TABLE `control_tests` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`control_id` integer NOT NULL,
	`at` integer NOT NULL,
	`result` text NOT NULL,
	`method` text NOT NULL,
	`tester_id` integer,
	`notes` text
);
--> statement-breakpoint
CREATE INDEX `control_tests_idx` ON `control_tests` (`control_id`,`at`);--> statement-breakpoint
CREATE TABLE `controls` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`name` text NOT NULL,
	`description` text,
	`owner_id` integer NOT NULL,
	`business_unit` text NOT NULL,
	`type` text NOT NULL,
	`frequency` text NOT NULL,
	`automated` integer NOT NULL,
	`key_control` integer DEFAULT true NOT NULL,
	`effectiveness` text DEFAULT 'not_tested' NOT NULL,
	`ccm_rule_id` text,
	`last_tested_at` integer,
	`created_at` integer DEFAULT (unixepoch() * 1000) NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `controls_ref_unique` ON `controls` (`ref`);--> statement-breakpoint
CREATE TABLE `csa_assessments` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`control_id` integer NOT NULL,
	`business_unit` text NOT NULL,
	`period` text NOT NULL,
	`due_at` integer NOT NULL,
	`submitted_at` integer,
	`submitted_by_id` integer,
	`result` text,
	`comment` text
);
--> statement-breakpoint
CREATE TABLE `customers` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`name` text NOT NULL,
	`type` text NOT NULL,
	`dob` text,
	`country` text NOT NULL,
	`country_risk` text NOT NULL,
	`business_type` text,
	`risk_rating` text NOT NULL,
	`kyc_status` text NOT NULL,
	`kyc_verified_at` integer,
	`pep` integer DEFAULT false NOT NULL,
	`sanctions_status` text NOT NULL,
	`adverse_media` integer DEFAULT false NOT NULL,
	`edd_status` text NOT NULL,
	`edd_completed_at` integer,
	`business_unit` text NOT NULL,
	`created_at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `customers_ref_unique` ON `customers` (`ref`);--> statement-breakpoint
CREATE TABLE `employees` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`name` text NOT NULL,
	`dob` text,
	`department` text NOT NULL,
	`job_title` text NOT NULL,
	`business_unit` text NOT NULL,
	`start_date` integer NOT NULL,
	`vetting_type` text NOT NULL,
	`vetting_status` text NOT NULL,
	`vetting_cleared_at` integer,
	`terminated_at` integer
);
--> statement-breakpoint
CREATE UNIQUE INDEX `employees_ref_unique` ON `employees` (`ref`);--> statement-breakpoint
CREATE TABLE `esg_metrics` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`framework` text NOT NULL,
	`pillar` text NOT NULL,
	`name` text NOT NULL,
	`unit` text NOT NULL,
	`value` real,
	`target` real,
	`lower_is_better` integer DEFAULT true NOT NULL,
	`period` text NOT NULL,
	`status` text NOT NULL,
	`evidence_note` text,
	`owner_id` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `evidence` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`entity_type` text NOT NULL,
	`entity_id` integer NOT NULL,
	`filename` text NOT NULL,
	`mime` text NOT NULL,
	`size` integer NOT NULL,
	`sha256` text NOT NULL,
	`storage_key` text NOT NULL,
	`uploaded_by_id` integer NOT NULL,
	`uploaded_at` integer NOT NULL
);
--> statement-breakpoint
CREATE INDEX `evidence_entity_idx` ON `evidence` (`entity_type`,`entity_id`);--> statement-breakpoint
CREATE TABLE `incidents` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`title` text NOT NULL,
	`description` text,
	`type` text NOT NULL,
	`severity` text NOT NULL,
	`status` text NOT NULL,
	`business_unit` text NOT NULL,
	`reported_by_id` integer NOT NULL,
	`owner_id` integer,
	`occurred_at` integer NOT NULL,
	`reported_at` integer NOT NULL,
	`rca_method` text,
	`root_cause` text,
	`notifiable` integer DEFAULT false NOT NULL,
	`regulator` text,
	`notification_due_at` integer,
	`notified_at` integer,
	`lessons_learned` text,
	`source_alert_id` integer,
	`closed_at` integer,
	`closed_by_id` integer
);
--> statement-breakpoint
CREATE UNIQUE INDEX `incidents_ref_unique` ON `incidents` (`ref`);--> statement-breakpoint
CREATE TABLE `integrations` (
	`id` text PRIMARY KEY NOT NULL,
	`name` text NOT NULL,
	`method` text NOT NULL,
	`direction` text NOT NULL,
	`frequency` text NOT NULL,
	`data_elements` text NOT NULL,
	`status` text NOT NULL,
	`last_sync_at` integer,
	`records_ingested` integer DEFAULT 0 NOT NULL,
	`data_quality` real
);
--> statement-breakpoint
CREATE TABLE `kris` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`risk_id` integer NOT NULL,
	`name` text NOT NULL,
	`unit` text NOT NULL,
	`value` real NOT NULL,
	`amber` real NOT NULL,
	`red` real NOT NULL,
	`history` text NOT NULL,
	`updated_at` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `links` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`from_type` text NOT NULL,
	`from_id` integer NOT NULL,
	`to_type` text NOT NULL,
	`to_id` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `links_unique` ON `links` (`from_type`,`from_id`,`to_type`,`to_id`);--> statement-breakpoint
CREATE TABLE `notifications` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`user_id` integer NOT NULL,
	`at` integer NOT NULL,
	`severity` text NOT NULL,
	`title` text NOT NULL,
	`body` text,
	`link` text,
	`read_at` integer
);
--> statement-breakpoint
CREATE INDEX `notif_user_idx` ON `notifications` (`user_id`,`read_at`);--> statement-breakpoint
CREATE TABLE `obligations` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`title` text NOT NULL,
	`source_type` text NOT NULL,
	`framework` text NOT NULL,
	`regulator` text,
	`jurisdiction` text NOT NULL,
	`business_unit` text,
	`owner_id` integer NOT NULL,
	`status` text NOT NULL,
	`last_assessed_at` integer,
	`next_review_at` integer
);
--> statement-breakpoint
CREATE UNIQUE INDEX `obligations_ref_unique` ON `obligations` (`ref`);--> statement-breakpoint
CREATE TABLE `policies` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`title` text NOT NULL,
	`summary` text,
	`owner_id` integer NOT NULL,
	`version` integer DEFAULT 1 NOT NULL,
	`status` text NOT NULL,
	`jurisdiction` text NOT NULL,
	`frameworks` text NOT NULL,
	`review_date` integer NOT NULL,
	`approved_by_id` integer,
	`approved_at` integer,
	`created_at` integer DEFAULT (unixepoch() * 1000) NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `policies_ref_unique` ON `policies` (`ref`);--> statement-breakpoint
CREATE TABLE `policy_acknowledgements` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`policy_id` integer NOT NULL,
	`user_id` integer NOT NULL,
	`version` integer NOT NULL,
	`at` integer NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `ack_unique` ON `policy_acknowledgements` (`policy_id`,`user_id`,`version`);--> statement-breakpoint
CREATE TABLE `policy_versions` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`policy_id` integer NOT NULL,
	`version` integer NOT NULL,
	`status` text NOT NULL,
	`note` text,
	`actor_id` integer NOT NULL,
	`at` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `programme_phases` (
	`id` integer PRIMARY KEY NOT NULL,
	`name` text NOT NULL,
	`start_week` integer NOT NULL,
	`end_week` integer NOT NULL,
	`scope` text NOT NULL,
	`gate` text NOT NULL,
	`status` text NOT NULL,
	`signed_off_at` integer
);
--> statement-breakpoint
CREATE TABLE `programme_tasks` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`phase_id` integer NOT NULL,
	`title` text NOT NULL,
	`done` integer DEFAULT false NOT NULL,
	`done_at` integer
);
--> statement-breakpoint
CREATE TABLE `regulatory_reports` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` text NOT NULL,
	`regulator` text NOT NULL,
	`frequency` text NOT NULL,
	`due_at` integer NOT NULL,
	`automated` integer NOT NULL,
	`prepared_at` integer,
	`submitted_at` integer,
	`submitted_by_id` integer,
	`reference` text,
	`owner_id` integer NOT NULL
);
--> statement-breakpoint
CREATE TABLE `risks` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`title` text NOT NULL,
	`description` text,
	`category` text NOT NULL,
	`owner_id` integer NOT NULL,
	`business_unit` text,
	`impact` integer NOT NULL,
	`likelihood` integer NOT NULL,
	`treatment` text NOT NULL,
	`treatment_plan` text,
	`treatment_due_at` integer,
	`source` text DEFAULT 'manual' NOT NULL,
	`status` text DEFAULT 'open' NOT NULL,
	`escalated_to_board` integer DEFAULT false NOT NULL,
	`last_reviewed_at` integer,
	`created_at` integer DEFAULT (unixepoch() * 1000) NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `risks_ref_unique` ON `risks` (`ref`);--> statement-breakpoint
CREATE TABLE `suppliers` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`name` text NOT NULL,
	`services` text NOT NULL,
	`owner_id` integer NOT NULL,
	`country` text NOT NULL,
	`tier` text NOT NULL,
	`data_access` integer NOT NULL,
	`annual_spend` real NOT NULL,
	`dd_status` text NOT NULL,
	`questionnaire_score` integer,
	`sanctions_status` text NOT NULL,
	`monitoring_flags` text NOT NULL,
	`status` text NOT NULL,
	`last_review_at` integer,
	`next_review_at` integer,
	`contract_renewal_at` integer,
	`dpa_in_place` integer DEFAULT false NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `suppliers_ref_unique` ON `suppliers` (`ref`);--> statement-breakpoint
CREATE TABLE `transactions` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`ref` text NOT NULL,
	`customer_id` integer NOT NULL,
	`amount` real NOT NULL,
	`currency` text NOT NULL,
	`direction` text NOT NULL,
	`counterparty` text NOT NULL,
	`counterparty_country` text NOT NULL,
	`at` integer NOT NULL,
	`screening_status` text NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `transactions_ref_unique` ON `transactions` (`ref`);--> statement-breakpoint
CREATE INDEX `tx_customer_at_idx` ON `transactions` (`customer_id`,`at`);--> statement-breakpoint
CREATE TABLE `users` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`email` text NOT NULL,
	`name` text NOT NULL,
	`role` text NOT NULL,
	`business_unit` text,
	`password_hash` text NOT NULL,
	`mfa_secret` text,
	`mfa_enabled` integer DEFAULT false NOT NULL,
	`failed_logins` integer DEFAULT 0 NOT NULL,
	`locked_until` integer,
	`active` integer DEFAULT true NOT NULL,
	`last_login_at` integer,
	`created_at` integer DEFAULT (unixepoch() * 1000) NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `users_email_unique` ON `users` (`email`);