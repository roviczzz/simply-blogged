# Tasks

## 1. Foundation & Project Scaffolding

- [x] 1.1 Scaffold fresh Laravel application files in the repository root configured for SQLite and verify `artisan` command execution.
- [x] 1.2 Install `laravel/boost` as a dev dependency, execute `php artisan boost:install`, and verify generated agent guidelines and MCP configuration.
- [x] 1.3 Create public storage symlink with `php artisan storage:link` and verify `public/storage` target directory exists.

## 2. Data Models, Migrations & Seeding

- [x] 2.1 Create database migrations for `posts`, `tags`, and `post_tag` tables with necessary indexes, foreign keys, and status fields; verify with `php artisan migrate`.
- [x] 2.2 Implement `Post` and `Tag` models with relationships, query scopes (`published()`), reading time calculation, and Markdown HTML accessor; verify with unit and model tests.
- [x] 2.3 Create database seeders for the administrator user, sample tags, and example posts (published, scheduled, and draft); verify with `php artisan db:seed`.

## 3. Backoffice Administration (Filament)

- [x] 3.1 Install and configure `filament/filament` panel builder under `/admin` and verify login route renders successfully.
- [x] 3.2 Build `PostResource` in Filament with title, reactive slug, cover image file upload, Markdown editor, tag relationship picker, and publishing status controls; verify creating and updating a post via admin panel.
- [x] 3.3 Build `TagResource` in Filament for managing tags and verify tag CRUD operations in the admin panel.

## 4. Public Frontend (Blade + Tailwind CSS)

- [ ] 4.1 Implement base Blade layout (`<x-layout.app>`) and SEO OpenGraph meta component (`<x-layout.seo>`) with Tailwind CSS; verify responsive layout rendering.
- [ ] 4.2 Implement `HomeController` and view displaying published posts, reading time estimates, pagination, and tag badges; verify with HTTP feature test.
- [ ] 4.3 Implement `PostController` for `/posts/{slug}` showing rendered Markdown content, author metadata, and conditional Prism.js code syntax highlighting; verify 200 on published posts and 404 on drafts.
- [ ] 4.4 Implement `TagController` for `/tags/{slug}` to display filtered posts for a specific tag; verify tag filtering with HTTP feature test.
- [ ] 4.5 Implement `FeedController` delivering a valid RSS 2.0 XML document at `/feed`; verify XML headers and post item schema with an HTTP test.

## 5. Verification & Delivery

- [ ] 5.1 Run test suite with `php artisan test` and verify all public and model tests pass.
- [ ] 5.2 Build frontend assets with `npm run build` and perform end-to-end smoke test of public reading routes and Filament admin panel.
