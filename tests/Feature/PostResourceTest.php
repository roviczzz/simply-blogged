<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;
use Livewire\Livewire;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_list_page_shows_posts(): void
    {
        $posts = Post::factory()->count(3)->create();

        Livewire::test(ListPosts::class)
            ->assertOk()
            ->assertCanSeeTableRecords($posts);
    }

    public function test_post_can_be_created_as_draft_with_generated_slug_and_author(): void
    {
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'My First Post',
                'slug' => 'my-first-post',
                'body' => "# Hello\n\nSome **markdown**.",
                'is_published' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'my-first-post')->firstOrFail();

        $this->assertSame('My First Post', $post->title);
        $this->assertFalse($post->is_published);
        $this->assertSame($this->admin->id, $post->user_id);
        $this->assertFalse(Post::published()->whereKey($post->id)->exists());
    }

    public function test_cover_image_is_stored_on_public_disk(): void
    {
        Storage::fake('public');

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'With Cover',
                'slug' => 'with-cover',
                'body' => 'Body',
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'with-cover')->firstOrFail();

        $this->assertNotNull($post->cover_image);
        $this->assertStringStartsWith('covers/', $post->cover_image);
        $this->assertStringEndsWith('.webp', $post->cover_image);
        Storage::disk('public')->assertExists($post->cover_image);

        $contents = Storage::disk('public')->get($post->cover_image);
        $this->assertStringStartsWith('RIFF', $contents);
        $this->assertSame('WEBP', substr($contents, 8, 4));
    }

    public function test_cover_image_uses_configured_media_disk(): void
    {
        config(['blog.media_disk' => 'custom-media']);
        Storage::fake('custom-media');
        Storage::fake('public');

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Configured Media Disk',
                'slug' => 'configured-media-disk',
                'body' => 'Body',
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'configured-media-disk')->firstOrFail();

        Storage::disk('custom-media')->assertExists($post->cover_image);
        Storage::disk('public')->assertMissing($post->cover_image);
        $this->assertSame(
            Storage::disk('custom-media')->url($post->cover_image),
            $post->cover_image_url,
        );
    }

    public function test_cover_image_falls_back_to_legacy_cover_disk_setting(): void
    {
        config([
            'blog.media_disk' => null,
            'blog.cover_disk' => 'legacy-covers',
        ]);
        Storage::fake('legacy-covers');
        Storage::fake('public');

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Legacy Disk',
                'slug' => 'legacy-disk',
                'body' => 'Body',
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'legacy-disk')->firstOrFail();

        Storage::disk('legacy-covers')->assertExists($post->cover_image);
        Storage::disk('public')->assertMissing($post->cover_image);
        $this->assertSame(
            Storage::disk('legacy-covers')->url($post->cover_image),
            $post->cover_image_url,
        );
    }

    public function test_cover_image_up_to_10mb_is_accepted_and_converted_to_webp(): void
    {
        Storage::fake('public');

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Large Cover',
                'slug' => 'large-cover',
                'body' => 'Body',
                'cover_image' => UploadedFile::fake()->image('large-cover.jpg')->size(5120),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'large-cover')->firstOrFail();

        $this->assertNotNull($post->cover_image);
        $this->assertStringEndsWith('.webp', $post->cover_image);
        Storage::disk('public')->assertExists($post->cover_image);
    }

    public function test_cover_image_exceeding_size_limit_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Too Large',
                'slug' => 'too-large',
                'body' => 'Body',
                'cover_image' => UploadedFile::fake()->image('large.jpg')->size(10241),
            ])
            ->call('create')
            ->assertHasFormErrors(['cover_image']);
    }

    public function test_upload_validation_failure_returns_422_json_cleanly(): void
    {
        $signedUrl = (new GenerateSignedUploadUrl)->forLocal();

        $oversizedFile = UploadedFile::fake()->create('too-large.jpg', 15000);

        $response = $this->postJson($signedUrl, [
            'files' => [$oversizedFile],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('files.0');
    }

    public function test_tags_can_be_attached_to_a_post(): void
    {
        $tags = Tag::factory()->count(2)->create();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Tagged',
                'slug' => 'tagged',
                'body' => 'Body',
                'tags' => $tags->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'tagged')->firstOrFail();

        $this->assertEqualsCanonicalizing($tags->pluck('id')->all(), $post->tags->pluck('id')->all());
    }

    public function test_post_can_be_scheduled_for_the_future(): void
    {
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Scheduled',
                'slug' => 'scheduled',
                'body' => 'Body',
                'is_published' => true,
                'published_at' => now()->addWeek()->toDateTimeString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'scheduled')->firstOrFail();

        $this->assertTrue($post->is_published);
        $this->assertFalse(Post::published()->whereKey($post->id)->exists());
    }

    public function test_published_requires_publication_date(): void
    {
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'No Date',
                'slug' => 'no-date',
                'body' => 'Body',
                'is_published' => true,
                'published_at' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['published_at' => 'required']);
    }

    public function test_slug_must_be_unique(): void
    {
        Post::factory()->create(['slug' => 'taken']);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Another',
                'slug' => 'taken',
                'body' => 'Body',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_post_can_be_updated_and_published(): void
    {
        $post = Post::factory()->draft()->create(['title' => 'Old title']);

        Livewire::test(EditPost::class, ['record' => $post->getKey()])
            ->fillForm([
                'title' => 'New title',
                'is_published' => true,
                'published_at' => now()->subMinute()->toDateTimeString(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $post->refresh();

        $this->assertSame('New title', $post->title);
        $this->assertTrue(Post::published()->whereKey($post->id)->exists());
    }

    public function test_post_body_image_attachment_is_converted_to_webp_and_stored_under_attachments_directory(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('diagram.png');

        $test = Livewire::test(CreatePost::class)
            ->set('componentFileAttachments.data.body', $file)
            ->call('callSchemaComponentMethod', 'form.body', 'saveUploadedFileAttachmentAndGetUrl');

        $url = data_get($test->effects, 'returns.0');
        $this->assertNotNull($url);

        $storedFiles = Storage::disk('public')->allFiles('attachments');
        $this->assertCount(1, $storedFiles);

        $storedPath = $storedFiles[0];
        $this->assertStringStartsWith('attachments/', $storedPath);
        $this->assertStringEndsWith('.webp', $storedPath);

        $contents = Storage::disk('public')->get($storedPath);
        $this->assertStringStartsWith('RIFF', $contents);
        $this->assertSame('WEBP', substr($contents, 8, 4));

        $this->assertSame(Storage::disk('public')->url($storedPath), $url);
    }

    public function test_post_body_image_attachment_uses_configured_media_disk(): void
    {
        config(['blog.media_disk' => 'custom-media']);
        Storage::fake('custom-media');
        Storage::fake('public');

        $file = UploadedFile::fake()->image('screenshot.jpg');

        $test = Livewire::test(CreatePost::class)
            ->set('componentFileAttachments.data.body', $file)
            ->call('callSchemaComponentMethod', 'form.body', 'saveUploadedFileAttachmentAndGetUrl');

        $url = data_get($test->effects, 'returns.0');
        $this->assertNotNull($url);

        $storedFiles = Storage::disk('custom-media')->allFiles('attachments');
        $this->assertCount(1, $storedFiles);
        $storedPath = $storedFiles[0];

        Storage::disk('custom-media')->assertExists($storedPath);
        Storage::disk('public')->assertMissing($storedPath);

        $this->assertSame(Storage::disk('custom-media')->url($storedPath), $url);
    }

    public function test_post_body_image_attachment_resolves_supabase_storage_url_when_supabase_disk_configured(): void
    {
        config(['blog.media_disk' => 'supabase']);
        Storage::fake('supabase', [
            'url' => 'https://test-project.supabase.co/storage/v1/object/public/media',
        ]);

        $file = UploadedFile::fake()->image('photo.jpeg');

        $test = Livewire::test(CreatePost::class)
            ->set('componentFileAttachments.data.body', $file)
            ->call('callSchemaComponentMethod', 'form.body', 'saveUploadedFileAttachmentAndGetUrl');

        $url = data_get($test->effects, 'returns.0');
        $this->assertNotNull($url);

        $storedFiles = Storage::disk('supabase')->allFiles('attachments');
        $this->assertCount(1, $storedFiles);
        $storedPath = $storedFiles[0];

        $this->assertStringStartsWith('https://test-project.supabase.co/storage/v1/object/public/media/attachments/', $url);
        $this->assertSame(Storage::disk('supabase')->url($storedPath), $url);
    }

    public function test_post_body_attachment_exceeding_10mb_is_rejected(): void
    {
        Storage::fake('public');

        $largeFile = UploadedFile::fake()->image('huge.png')->size(10241);

        $test = Livewire::test(CreatePost::class)
            ->set('componentFileAttachments.data.body', $largeFile)
            ->call('callSchemaComponentMethod', 'form.body', 'saveUploadedFileAttachmentAndGetUrl');

        $this->assertNull(data_get($test->effects, 'returns.0'));
        $this->assertEmpty(Storage::disk('public')->allFiles('attachments'));
    }

    public function test_post_body_attachment_with_invalid_mime_type_is_rejected(): void
    {
        Storage::fake('public');

        $nonImageFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $test = Livewire::test(CreatePost::class)
            ->set('componentFileAttachments.data.body', $nonImageFile)
            ->call('callSchemaComponentMethod', 'form.body', 'saveUploadedFileAttachmentAndGetUrl');

        $this->assertNull(data_get($test->effects, 'returns.0'));
        $this->assertEmpty(Storage::disk('public')->allFiles('attachments'));
    }

    public function test_post_body_markdown_editor_attachment_configuration(): void
    {
        $test = Livewire::test(CreatePost::class);
        $bodyComponent = $test->instance()->getSchema('form')->getComponent('body');

        $this->assertNotNull($bodyComponent);
        $this->assertSame('attachments', $bodyComponent->getFileAttachmentsDirectory());
        $this->assertSame(10240, $bodyComponent->getFileAttachmentsMaxSize());
        $this->assertSame(['image/jpeg', 'image/png', 'image/webp'], $bodyComponent->getFileAttachmentsAcceptedFileTypes());
    }
}
