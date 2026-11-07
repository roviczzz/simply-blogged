# Tasks

## 1. Model & View Hardening

- [x] 1.1 Configure `withDefault(['name' => 'Anonymous'])` on `author()` in `app/Models/Post.php` and verify that calling `$post->author->name` on an orphaned post returns the fallback without throwing an exception.
- [x] 1.2 Update author rendering in `resources/views/posts/show.blade.php` to conditionally render the author line when `$post->author?->exists` is true, ensuring clean metadata formatting when author is missing.

## 2. Test Coverage & Verification

- [x] 2.1 Add a feature test in `tests/Feature/PostPageTest.php` verifying that a published post referencing a nonexistent author renders with HTTP 200 and omits the author line without error.
- [x] 2.2 Run Pint code formatter (`vendor/bin/pint --dirty --format agent`) and execute `php artisan test --compact` to verify all tests pass without regressions.
