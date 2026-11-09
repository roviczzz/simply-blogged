# Tasks

## 1. Continuous Integration Workflow

- [x] 1.1 Create `.github/workflows/ci.yml` configured for `push` and `pull_request` on default branches (`main`, `master`) plus `workflow_dispatch`, including PHP 8.4 setup (with `gd`, `pdo_sqlite`, `intl`, `exif`, `zip`), Composer caching, strict Pint code style check (`vendor/bin/pint --test`), Node 22 frontend build (`npm ci && npm run build`), and PHPUnit test execution using in-memory SQLite, and verify the YAML syntax is valid.
- [x] 1.2 Run `vendor/bin/pint --test` and `npm run build` locally to verify that the CI command sequence matches the current codebase state and exits cleanly.

## 2. Infrastructure Keep-Alive Workflows

- [x] 2.1 Create `.github/workflows/keep-alive-render.yml` configured with a 14-minute cron schedule (`*/14 * * * *`) and `workflow_dispatch`, implementing a curl GET request to `${{ secrets.RENDER_URL }}/up` with `--max-time 90`, `-L`, and HTTP status code verification (200-399), and verify YAML syntax.
- [x] 2.2 Create `.github/workflows/wake-up-supabase.yml` configured with a daily cron schedule (`0 6 * * *`) and `workflow_dispatch`, implementing the Python 3 probe script targeting `/rest/v1/${{ secrets.SUPABASE_TABLE || 'posts' }}?select=*&limit=1` and `/auth/v1/health` with support for both `SUPABASE_PROJECTS_JSON` and `SUPABASE_URL`/`SUPABASE_ANON_KEY`, and verify YAML syntax.

## 3. Secret Configuration & Documentation

- [x] 3.1 Document required GitHub repository secrets (`RENDER_URL`, `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_TABLE`, `SUPABASE_PROJECTS_JSON`) and maintenance considerations (such as private repository minutes) in `README.md` or deployment documentation, and verify documentation completeness.
- [x] 3.2 Perform end-to-end linting and syntax validation across all three workflow files to confirm compliance with GitHub Actions schema requirements.
