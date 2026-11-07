# Design: Handle Missing Post Authors

## Context

In `App\Models\Post`, the `author()` relationship defines a `BelongsTo` to `User`. When a post's `user_id` does not match an existing record in the `users` table (such as after an author is deleted or in local test/seed scenarios), `$post->author` evaluates to `null`. In `resources/views/posts/show.blade.php:13`, the template directly accesses `$post->author->name`, throwing a fatal 500 error (`Attempt to read property "name" on null`).

See `proposal.md` for background and motivation.

## Goals / Non-Goals

**Goals:**
- Guarantee that `$post->author` does not resolve to `null`, preventing uncaught property-access errors across all templates, controllers, and jobs.
- Defensively format author information in `resources/views/posts/show.blade.php` so the layout remains clean whether an author exists or is omitted.
- Add automated test coverage asserting that posts with missing or deleted authors render cleanly with HTTP 200.

**Non-Goals:**
- Changing database migrations or adding nullable foreign key columns.
- Building an admin author reassignment UI or changing Filament delete actions.

## Decisions

### Decision 1: Use Eloquent `withDefault()` on `Post::author()`
- **Choice**: Configure `return $this->belongsTo(User::class, 'user_id')->withDefault(['name' => 'Anonymous']);` in `App\Models\Post`.
- **Rationale**: `withDefault()` is Laravel's native mechanism for ensuring `BelongsTo` relationships return a default model instance rather than `null`. This prevents fatal errors anywhere `$post->author->name` is accessed.
- **Alternatives considered**:
  - *Custom Accessor (`$post->author_name`)*: Requires refactoring existing call sites and relationship usages.
  - *Blade-only null checks*: Leaves other potential call sites (e.g. future feeds, APIs, emails) unprotected.

### Decision 2: Defensive Blade Rendering in `posts/show.blade.php`
- **Choice**: In `resources/views/posts/show.blade.php`, conditionally render the author section only when the author is present and has a valid record (`$post->author?->exists`), or safely coalesce `$post->author?->name`. If the author record does not exist in the database, omit the "By ..." section cleanly so the date and read-time metadata align seamlessly.
- **Rationale**: For an article whose author was deleted or is missing, showing `November 7, 2026 · 1 min read` is cleaner and more professional than showing `By Anonymous`.

## Risks / Trade-offs

- **[Risk]** With `withDefault()`, `$post->author` is never `null`, making `@if ($post->author)` always evaluate to `true`.
  - **Mitigation**: Check `$post->author?->exists` when checking if the author corresponds to an actual persisted database record.
