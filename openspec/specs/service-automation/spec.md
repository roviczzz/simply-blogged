# service-automation Specification

## Purpose

Automates continuous integration code quality gating (style checks, frontend compilation, automated testing) and ensures external deployment availability through scheduled keep-alive probes for Render and Supabase.

## Requirements

### Requirement: Automated Code Style Verification
The CI workflow SHALL verify that all PHP codebase modifications adhere to project formatting standards using Laravel Pint in dry-run test mode on pull requests and pushes to default branches.

#### Scenario: Unformatted PHP code triggers CI failure
- **WHEN** a pull request or commit contains PHP code with Pint style violations
- **THEN** the CI workflow execution fails at the Pint verification step with a non-zero exit code

#### Scenario: Properly formatted PHP code passes style check
- **WHEN** all PHP code matches configured Pint rules
- **THEN** the Pint verification step completes successfully with exit code 0

### Requirement: Automated Frontend Asset Compilation Verification
The CI workflow SHALL compile frontend JavaScript and Tailwind CSS assets using Node 22 and Vite to verify build integrity prior to test execution.

#### Scenario: Clean frontend build
- **WHEN** valid frontend code is pushed
- **THEN** `npm ci` and `npm run build` execute successfully and produce the production asset manifest in `public/build`

### Requirement: Automated Test Suite Execution
The CI workflow SHALL execute all PHPUnit unit and feature tests against an isolated in-memory SQLite database and required PHP extensions including GD with WebP support.

#### Scenario: All tests pass
- **WHEN** the test suite executes on PHP 8.4 with required extensions
- **THEN** all PHPUnit feature and unit tests pass and the job reports success

#### Scenario: Test failure blocks CI
- **WHEN** any unit or feature test assertion fails
- **THEN** the CI job terminates with failure status and marks the commit check as failed

### Requirement: Render Web Service Keep-Alive Automation
The automation system SHALL send periodic HTTP requests to the Render web service's `/up` health route every 14 minutes and on manual dispatch to prevent container spin-down.

#### Scenario: Scheduled ping succeeds
- **WHEN** the 14-minute cron schedule fires or workflow_dispatch is manually triggered
- **THEN** an HTTP GET request is sent to `${RENDER_URL}/up` with a 90-second timeout and passes if the response status is between 200 and 399

#### Scenario: Missing configuration alert
- **WHEN** the `RENDER_URL` secret is missing or empty
- **THEN** the workflow fails with an explicit error indicating that `RENDER_URL` is unconfigured

### Requirement: Supabase Project Keep-Alive Automation
The automation system SHALL probe Supabase database and authentication endpoints on a daily schedule and on manual dispatch to prevent free-tier project pausing.

#### Scenario: Daily multi-endpoint probe succeeds
- **WHEN** the scheduled cron (daily at 06:00 UTC) fires or workflow_dispatch is triggered
- **THEN** the probe sends authenticated GET requests to `/rest/v1/{table}?select=*&limit=1` and `/auth/v1/health` and verifies 2xx status codes

#### Scenario: Missing Supabase credentials alert
- **WHEN** neither `SUPABASE_PROJECTS_JSON` nor both `SUPABASE_URL` and `SUPABASE_ANON_KEY` are provided
- **THEN** the probe fails immediately with an error message instructing the user to configure repository secrets
