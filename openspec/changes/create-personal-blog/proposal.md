# Proposal

## Why

Establish a fast, clean, and easily maintainable personal blog website built on modern Laravel. The system needs an intuitive backoffice to author and publish Markdown articles with local media, paired with an ultra-lean, blazing-fast public presentation layer free from client-side framework bloat.

## What Changes

- Initialize a modern Laravel application configured with SQLite and local file storage.
- Install and configure **Filament** (`/admin`) for administrative authentication, post CRUD, tag management, and rich Markdown authoring.
- Implement the public blog frontend using pure Blade components and Tailwind CSS for the homepage feed, post reading view, and tag filtering.
- Support client-side code syntax highlighting (via Prism.js) for code blocks in Markdown articles.
- Provide a native RSS syndication feed (`/feed`) rendered directly via Blade XML.
- Install and configure **Laravel Boost** (`laravel/boost` in `--dev`) to provide MCP and AI guidelines without production runtime impact.

## Capabilities

### New Capabilities

- `post-publishing`: Administrative management and authoring of blog posts, tags, cover images, scheduling, and Markdown formatting via Filament.
- `public-blog`: Public-facing presentation of blog posts, tag archives, client-side code highlighting, and RSS feed syndication using pure Blade and Tailwind CSS.

### Modified Capabilities

*(None - greenfield project with no pre-existing specs)*

## Impact

- **Dependencies**: Adds `filament/filament` for admin panel management and `laravel/boost` as a dev dependency.
- **Database**: SQLite database with migrations for `users`, `posts`, `tags`, and `post_tag`.
- **Filesystem**: Configured symbolic link (`storage/app/public` to `public/storage`) for local image uploads.
- **Performance**: High-speed server-rendered HTML with zero JavaScript framework bundle overhead for public readers.
