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

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
