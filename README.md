<p align="center">
  <img src="public/simply-blogged.webp" width="96" height="96" alt="Simply Blogged Logo" style="border-radius: 16px;">
</p>

<h1 align="center">Simply Blogged</h1>

<p align="center">
  A modern, minimalist content publishing platform and blog engine built with Laravel and Filament.
</p>

---

## Overview

**Simply Blogged** is a performance-focused publishing platform engineered to provide an elegant, distraction-free reading experience alongside a seamless editorial workflow. Crafted with a typography-first design, it allows authors to compose articles using Markdown, automatically optimizes media into modern formats, and syndicates posts via an integrated RSS feed.

A dedicated administrative back-office powered by Filament enables effortless article authoring, category and taxonomy management, scheduled publishing, and metadata control.

---

## Key Features

- **Typography-First Reader Experience:** Clean, responsive design supporting both light and dark themes.
- **Filament Admin Panel:** Intuitive administration interface for creating, editing, and organizing posts and tags.
- **Markdown & Code Highlighting:** Rich Markdown rendering with integrated Prism.js syntax highlighting for developer-centric content.
- **Automated Image Optimization:** Automatic on-upload conversion of cover images to modern WebP format, preserving storage and bandwidth.
- **Content Syndication:** Built-in RSS feed generation (`/feed`) for syndication readers.
- **SEO & Social Sharing:** Automatic Open Graph and Twitter card meta tag generation, canonical URLs, and estimated reading times.
- **CLI Utility:** Dedicated Artisan command (`posts:convert-covers-to-webp`) to bulk-convert existing cover images to WebP with dry-run support.

---

## Tech Stack

- **Framework:** [Laravel 13](https://laravel.com/)
- **Language:** [PHP 8.3+](https://www.php.net/)
- **Admin Panel & CMS:** [Filament 5](https://filamentphp.com/)
- **Frontend & Styling:** Blade Components & [Tailwind CSS v4](https://tailwindcss.com/)
- **Build Tooling:** [Vite](https://vitejs.dev/)
- **Syntax Highlighting:** [Prism.js](https://prismjs.com/)
- **Testing:** [PHPUnit](https://phpunit.de/)

---

## Getting Started

### Prerequisites

- PHP >= 8.3 with SQLite, GD/Imagick, and standard Laravel extensions enabled
- Composer
- Node.js (v18+) & npm

### Automated Setup

You can run the built-in setup script to install dependencies, generate application keys, and build assets:

```bash
composer run setup
```

### Manual Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/simply-blogged.git
   cd simply-blogged
   ```

2. **Install PHP and Node dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure the environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Prepare the database and storage:**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Build frontend assets:**
   ```bash
   npm run build
   ```

---

## Development

Start both the PHP development server and Vite asset watcher concurrently:

```bash
composer run dev
```

Visit the application at:
- **Public Blog:** [http://localhost:8000](http://localhost:8000)
- **Admin Panel:** [http://localhost:8000/admin](http://localhost:8000/admin)

### Default Admin Credentials

When the database is seeded (`AdminUserSeeder`), a default administrative account is provisioned:

- **Email:** `admin@example.com` (configurable via `ADMIN_EMAIL` in `.env`)
- **Password:** `password` (configurable via `ADMIN_PASSWORD` in `.env`)

> *Note: In production environments, `ADMIN_PASSWORD` must be explicitly set to a secure value.*

---

## Artisan Commands

### Convert Existing Cover Images to WebP

Bulk converts legacy cover images to WebP format:

```bash
# Preview changes without modifying files or database records
php artisan posts:convert-covers-to-webp --dry-run

# Run the conversion
php artisan posts:convert-covers-to-webp
```

---

## Testing & Quality

Run the test suite:

```bash
composer test
# or
php artisan test
```

Format code according to Laravel standards:

```bash
vendor/bin/pint --format agent
```

---

## CI/CD & Automation

The repository includes GitHub Actions workflows configured in `.github/workflows/`:

1. **Continuous Integration (`ci.yml`)**:
   - Triggers automatically on `push` and `pull_request` targeting `main` or `master`, and on manual dispatch (`workflow_dispatch`).
   - Runs on PHP 8.4 with required extensions (`gd`, `pdo_sqlite`, `intl`, `exif`, `zip`).
   - Executes Laravel Pint style validation (`vendor/bin/pint --test`).
   - Compiles frontend assets with Node 22 (`npm ci && npm run build`).
   - Executes the complete PHPUnit test suite against an in-memory SQLite database.

2. **Render Web Service Keep-Alive (`keep-alive-render.yml`)**:
   - Runs every 14 minutes (`*/14 * * * *`) and on manual dispatch (`workflow_dispatch`).
   - Sends an HTTP GET request to `${RENDER_URL}/up` with a 90-second timeout to prevent Render free-tier instances from spinning down due to inactivity.

3. **Supabase Keep-Alive (`wake-up-supabase.yml`)**:
   - Runs daily at 06:00 UTC (`0 6 * * *`) and on manual dispatch (`workflow_dispatch`).
   - Probes both REST API (`/rest/v1/{table}?select=*&limit=1`) and Auth API (`/auth/v1/health`) to prevent Supabase free-tier project pausing after 7 days of inactivity.

### Required Repository Secrets

Configure the following secrets in GitHub under **Settings > Secrets and variables > Actions**:

| Secret | Description | Required | Default |
| --- | --- | --- | --- |
| `RENDER_URL` | Full base URL of your Render service (e.g., `https://simply-blogged.onrender.com`) | Yes (for Render keep-alive) | — |
| `SUPABASE_URL` | Supabase project URL (e.g., `https://xyzcompany.supabase.co`) | Yes (for single-project Supabase probe) | — |
| `SUPABASE_ANON_KEY` | Supabase public anonymous API key | Yes (for single-project Supabase probe) | — |
| `SUPABASE_TABLE` | Database table to query during the REST probe | No | `posts` |
| `SUPABASE_PROJECTS_JSON` | JSON array of Supabase projects: `[{"url": "https://...", "anon_key": "...", "table": "posts"}]` | No (alternative for multi-project keep-alive) | — |

### Maintenance & Private Repository Considerations

- **GitHub Actions Minutes**: Private repositories include 2,000 free runner minutes per month. A 14-minute cron schedule consumes approximately 3,000 minutes monthly (since GitHub rounds each job execution up to the nearest whole minute). If your repository is private and minutes are constrained, consider:
  - Adjusting the Render cron schedule in `.github/workflows/keep-alive-render.yml` to a longer interval.
  - Making the repository public (which has unlimited Actions minutes).
  - Using an external free uptime monitoring service (such as [cron-job.org](https://cron-job.org) or [UptimeRobot](https://uptimerobot.com)) pointing to `<your-render-url>/up`.
- **Manual Verification**: All workflows support `workflow_dispatch`, allowing you to trigger runs on demand from the GitHub Actions tab to verify credentials and endpoint availability.

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
