# Proposal: Handle Missing Post Authors

## Why

If a blog post references an author account that has been deleted or does not exist in the database, navigating to `/posts/{slug}` crashes with a 500 Internal Server Error (`Attempt to read property "name" on null`). The public blog reading experience should be resilient against missing author records, ensuring readers can still view articles without server exceptions.

## What Changes

- Add fallback attributes to `Post::author()` using Eloquent's `withDefault()` to ensure property access on the author relationship remains safe across the application.
- Update `resources/views/posts/show.blade.php` to defensively check for author presence before rendering the author line, preventing null dereferencing.
- Add feature tests in `tests/Feature/PostPageTest.php` verifying that published posts with missing or deleted authors render successfully with HTTP 200.

## Capabilities

### New Capabilities

### Modified Capabilities
- `public-blog`: Update post detail view requirements to gracefully handle posts whose authors are nonexistent or deleted.

## Impact

- Affected code: `app/Models/Post.php`, `resources/views/posts/show.blade.php`.
- Affected tests: `tests/Feature/PostPageTest.php`.
- Zero database schema changes and zero breaking API changes.
