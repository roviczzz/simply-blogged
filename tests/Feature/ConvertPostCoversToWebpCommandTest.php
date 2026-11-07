<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConvertPostCoversToWebpCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->author = User::factory()->create();
    }

    public function test_converts_existing_non_webp_covers_to_webp_and_updates_database_records(): void
    {
        $fakeJpeg = UploadedFile::fake()->image('original.jpg', 100, 100);
        Storage::disk('public')->put('covers/original.jpg', $fakeJpeg->getContent());

        $post = Post::factory()->create([
            'user_id' => $this->author->id,
            'cover_image' => 'covers/original.jpg',
        ]);

        $this->artisan('posts:convert-covers-to-webp')
            ->expectsOutputToContain('Converted Post #'.$post->id)
            ->assertSuccessful();

        $post->refresh();

        $this->assertStringStartsWith('covers/', $post->cover_image);
        $this->assertStringEndsWith('.webp', $post->cover_image);
        $this->assertNotSame('covers/original.jpg', $post->cover_image);

        Storage::disk('public')->assertExists($post->cover_image);
        Storage::disk('public')->assertMissing('covers/original.jpg');

        $content = Storage::disk('public')->get($post->cover_image);
        $this->assertStringStartsWith('RIFF', $content);
        $this->assertSame('WEBP', substr($content, 8, 4));
    }

    public function test_dry_run_flag_does_not_mutate_disk_or_database(): void
    {
        $fakePng = UploadedFile::fake()->image('preview.png', 100, 100);
        Storage::disk('public')->put('covers/preview.png', $fakePng->getContent());

        $post = Post::factory()->create([
            'user_id' => $this->author->id,
            'cover_image' => 'covers/preview.png',
        ]);

        $this->artisan('posts:convert-covers-to-webp', ['--dry-run' => true])
            ->expectsOutputToContain('Dry run mode enabled')
            ->expectsOutputToContain('Would convert: Post #'.$post->id)
            ->assertSuccessful();

        $post->refresh();

        $this->assertSame('covers/preview.png', $post->cover_image);
        Storage::disk('public')->assertExists('covers/preview.png');
    }

    public function test_ignores_posts_already_using_webp_covers(): void
    {
        Post::factory()->create([
            'user_id' => $this->author->id,
            'cover_image' => 'covers/existing.webp',
        ]);

        $this->artisan('posts:convert-covers-to-webp')
            ->expectsOutput('No posts found with non-WebP cover images.')
            ->assertSuccessful();
    }

    public function test_skips_posts_with_missing_source_files_gracefully(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->author->id,
            'cover_image' => 'covers/missing.jpg',
        ]);

        $this->artisan('posts:convert-covers-to-webp')
            ->expectsOutputToContain('Source image [covers/missing.jpg] not found on public disk. Skipping.')
            ->assertSuccessful();

        $post->refresh();
        $this->assertSame('covers/missing.jpg', $post->cover_image);
    }
}
