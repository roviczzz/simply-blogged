# Proposal

## Why

The Simply Blogged application currently lacks automated GitHub Actions workflows. Pull requests and commits are not automatically validated against code style standards, frontend build checks, or the PHPUnit test suite before deployment, risking regressions. Additionally, the application is deployed on Render's free tier, which spins down after 15 minutes of inactivity, and relies on Supabase, which pauses inactive projects after 7 days without queries.

Implementing dedicated GitHub Actions workflows establishes automated quality gating for every push and pull request, prevents Render cold starts via frequent scheduled health pings, and keeps Supabase instances active using scheduled multi-endpoint probes.

## What Changes

- Add `.github/workflows/ci.yml` to automatically run code style validation with Laravel Pint (`vendor/bin/pint --test`), build Vite/Tailwind 4 frontend assets (`npm ci && npm run build`), and execute the PHPUnit test suite against an in-memory SQLite database on every push and pull request to default branches (`main`, `master`).
- Add `.github/workflows/keep-alive-render.yml` to ping the Render deployment's `/up` health check endpoint every 14 minutes on schedule (`*/14 * * * *`) and on manual trigger (`workflow_dispatch`), using a resilient HTTP client with timeout handling to prevent container spin-down.
- Add `.github/workflows/wake-up-supabase.yml` to wake up Supabase projects on a daily schedule (`0 6 * * *`) and on manual trigger (`workflow_dispatch`), executing a Python script that probes `/rest/v1/{table}?select=*&limit=1` and `/auth/v1/health` with support for both single-project secrets and `SUPABASE_PROJECTS_JSON`.
- Update repository documentation or configuration examples where appropriate to document required GitHub Actions repository secrets (`RENDER_URL`, `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_TABLE`, `SUPABASE_PROJECTS_JSON`).

## Capabilities

### New Capabilities
- `service-automation`: Defines the requirements for continuous integration validation (Pint formatting enforcement, asset compilation, and test execution) and scheduled infrastructure keep-alive automation (Render web service pings and Supabase database/auth probes).

### Modified Capabilities
<!-- None: existing blog capabilities (admin-provisioning, post-publishing, public-blog) requirements remain unchanged -->

## Impact

- **GitHub Workflows**: Creates three new workflow files in `.github/workflows/` (`ci.yml`, `keep-alive-render.yml`, `wake-up-supabase.yml`).
- **Dependencies & Tools**: No changes to `composer.json` or `package.json`. Workflows use standard GitHub-hosted `ubuntu-latest` runners, `shivammathur/setup-php@v2` (PHP 8.4), and `actions/setup-node@v4` (Node 22).
- **Secrets & Configuration**: Requires configuring GitHub repository secrets for live environment URLs and Supabase API credentials.
- **Backward Compatibility**: Fully non-breaking. Local development, Docker builds, and Artisan commands remain unchanged.
