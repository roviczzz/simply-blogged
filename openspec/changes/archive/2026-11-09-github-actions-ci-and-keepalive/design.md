# Design

## Context

The application is built on Laravel (PHP 8.4 / 8.3) with Filament, Tailwind CSS 4, and Vite 8, containerized via Docker and deployed to Render with Supabase storage and database integration. The repository is private.

Key constraints:
- **Private Repository Minutes**: GitHub Actions provides 2,000 free minutes per month for private repositories, billed in whole-minute increments.
- **Render Inactivity Timeout**: Render free services spin down after 15 minutes of inactivity; cold starts take ~45–50 seconds.
- **Supabase Free Tier Rules**: Supabase projects pause after 7 days without database or API traffic.
- **Image Conversion Dependencies**: Feature tests convert uploaded cover images to WebP format, requiring the PHP `gd` extension with WebP support.

## Goals / Non-Goals

**Goals:**
- Provide automated CI testing on every push and pull request to ensure code style compliance, frontend asset build viability, and passing PHPUnit tests.
- Maintain Render service warmth via high-frequency health check pings without failing unpredictably during container cold boots.
- Maintain Supabase project activity via scheduled multi-endpoint probes (REST + Auth) supporting both single-project credentials and multi-project JSON arrays.
- Provide manual trigger capability (`workflow_dispatch`) across all scheduled workflows for immediate verification.

**Non-Goals:**
- Continuous Deployment (CD): Render handles its own container builds and deployment webhooks from Git commits.
- Production database migration execution from GitHub Actions.
- Managing external monitoring SaaS subscriptions (e.g., UptimeRobot / BetterStack).

## Decisions

### Decision 1: Three Dedicated Workflows over a Single Mega-Workflow
- **Choice**: Create three separate workflow files:
  1. `.github/workflows/ci.yml`
  2. `.github/workflows/keep-alive-render.yml`
  3. `.github/workflows/wake-up-supabase.yml`
- **Rationale**: 
  - CI runs exclusively on Git events (`push`, `pull_request`).
  - Render keep-alive runs on a rapid 14-minute cadence (`*/14 * * * *`).
  - Supabase keep-alive runs on a daily cadence (`0 6 * * *`).
  - Separating them prevents scheduled ping executions from cluttering the pull request check history or failing commit statuses due to external network blips.
- **Alternatives Considered**:
  - *Single combined workflow with conditional jobs*: Conflates scheduled and event-driven runs, resulting in noisy Action logs and complex `if:` conditions.
  - *Two workflows (CI + Combined Keep-Alive)*: Pinging Supabase every 14 minutes is unnecessary (7-day pause rule only requires periodic queries) and increases API calls unnecessarily.

### Decision 2: Streamlined Single-Job CI Pipeline with Strict Pint Validation
- **Choice**: Structure `ci.yml` as a single sequence of steps:
  1. PHP 8.4 setup (with `gd`, `pdo_sqlite`, `intl`, `exif`, `zip`).
  2. Node 22 setup with `npm ci && npm run build`.
  3. Composer dependency caching & installation.
  4. Code formatting check with `vendor/bin/pint --test` (fails build on violations).
  5. In-memory SQLite test execution via `php artisan test`.
- **Rationale**: Splitting CI into parallel jobs (e.g. separate Pint job and separate Test job) would duplicate checkout, PHP setup, and dependency caching, doubling runner minutes on a private repository where each job rounds up to a full minute.
- **Alternatives Considered**:
  - *Parallel lint and test jobs*: Faster wall-clock time by ~20 seconds, but consumes at least 2 billed minutes per push instead of 1.
  - *Non-fatal Pint check*: Allows style regressions into main. Strict `--test` mode enforces consistency before merge.

### Decision 3: Render Ping via Native Laravel `/up` with 90-Second Timeout
- **Choice**: Target `${RENDER_URL}/up` with curl using `-L` (follow redirects) and `--max-time 90`.
- **Rationale**: Laravel's built-in `/up` route returns an immediate HTTP 200 without executing expensive database queries or rendering Blade views. The 90-second timeout allows sleeping Render containers to finish spinning up (~45s) without triggering premature timeout failures.
- **Alternatives Considered**:
  - *Pinging the homepage (`/`)*: Renders the full view and database queries; heavier than `/up`.
  - *Default 10s curl timeout*: Times out and fails whenever Render is waking from a cold start.

### Decision 4: Python Probe Script for Supabase Wake-Up
- **Choice**: Embed the user's tested Python 3 probe script in `wake-up-supabase.yml`, querying `/rest/v1/${SUPABASE_TABLE:-posts}?select=*&limit=1` and `/auth/v1/health`.
- **Rationale**: Reuses proven, robust logic supporting both single-instance environment variables and multi-instance JSON arrays with Bearer token authentication and structured error output.
- **Alternatives Considered**:
  - *Rewriting probe in curl / bash*: Harder to cleanly parse multi-project JSON secrets and validate table regex patterns.

## Risks / Trade-offs

- **[Risk] Private Repository Minute Depletion**: A 14-minute cron consumes ~3,000 billed minutes per month, which exceeds the free 2,000-minute private allowance if left unmonitored.
  - **Mitigation**: The keep-alive job is stripped of unnecessary dependencies and finishes in < 15 seconds. Document clearly that users can adjust cron intervals or configure a free external ping service (such as UptimeRobot or cron-job.org) if GitHub Actions minutes approach the cap.
- **[Risk] GitHub Actions Cron Delay (Jitter)**: GitHub's scheduled runners can be delayed by 5–30 minutes during peak times, potentially allowing Render to enter a brief sleep state.
  - **Mitigation**: The 90s curl timeout ensures that when the runner does fire, it successfully wakes the service even if it had spun down.
- **[Risk] Supabase Table Misconfiguration**: Querying an invalid or non-existent table will result in a 404/400 HTTP error.
  - **Mitigation**: Default the table to `posts` (which is confirmed present in project migrations) and allow overriding via `SUPABASE_TABLE`.
- **[Risk] Missing WebP Support in CI Runner**: Image conversion tests fail if GD lacks WebP compilation.
  - **Mitigation**: Specify `gd` in `shivammathur/setup-php@v2`, which includes WebP support by default on Ubuntu runners.

## Migration Plan

1. Create workflow definitions in `.github/workflows/`:
   - `ci.yml`
   - `keep-alive-render.yml`
   - `wake-up-supabase.yml`
2. Add required repository secrets under GitHub Settings:
   - `RENDER_URL`
   - `SUPABASE_URL`
   - `SUPABASE_ANON_KEY`
   - Optional: `SUPABASE_TABLE` (defaults to `posts`)
   - Optional: `SUPABASE_PROJECTS_JSON`
3. Manually trigger both keep-alive workflows via GitHub UI (`workflow_dispatch`) to verify secret resolution and network responses.
4. Open a test Pull Request or push a commit to trigger `ci.yml` and verify Pint, asset compilation, and PHPUnit pass.
