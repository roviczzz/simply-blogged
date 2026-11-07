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
        Storage::disk('public')->assertExists($post->cover_image);
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
}
