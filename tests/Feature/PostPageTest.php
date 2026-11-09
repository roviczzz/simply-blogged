<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_post_renders_markdown_author_and_tags(): void
    {
        $author = User::factory()->create(['name' => 'Jane Writer']);
        $post = Post::factory()->for($author, 'author')->hasAttached(Tag::factory()->state(['name' => 'PHP']))->create([
            'title' => 'Reading Test',
            'body' => "## Subheading\n\nSome **bold** text.",
        ]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSeeText('Reading Test')
            ->assertSeeText('Jane Writer')
            ->assertSeeText('PHP')
            ->assertSee('<h2>Subheading</h2>', false)
            ->assertSee('<strong>bold</strong>', false)
            ->assertSee('property="og:type" content="article"', false);
    }

    public function test_post_page_renders_author_avatar_when_present(): void
    {
        $author = User::factory()->withAvatar()->create(['name' => 'Alice Illustrated']);
        $post = Post::factory()->for($author, 'author')->create([
            'title' => 'Illustrated Article',
            'body' => 'Body text here.',
        ]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSeeText('By Alice Illustrated')
            ->assertSee($author->avatar_url, false)
            ->assertSee('alt="Alice Illustrated"', false);
    }

    public function test_post_page_omits_avatar_image_when_author_has_no_avatar(): void
    {
        $author = User::factory()->create([
            'name' => 'Bob Simple',
            'avatar_path' => null,
        ]);
        $post = Post::factory()->for($author, 'author')->create([
            'title' => 'Unadorned Post',
            'body' => 'Clean typography.',
        ]);

        $response = $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSeeText('By Bob Simple');

        $response->assertDontSee('/storage/avatars/');
        $response->assertDontSee('alt="Bob Simple"', false);
    }

    public function test_post_page_loads_prism_for_code_blocks(): void
    {
        $post = Post::factory()->create(['body' => "```php\necho 1;\n```"]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('<code class="language-php">', false)
            ->assertSee('prism', false);
    }

    public function test_homepage_does_not_load_prism(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('prism', false);
    }

    public function test_post_page_does_not_render_raw_html_from_markdown(): void
    {
        $post = Post::factory()->create(['body' => '<script>alert(1)</script>']);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_draft_post_returns_not_found(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get(route('posts.show', $post->slug))->assertNotFound();
    }

    public function test_scheduled_post_returns_not_found(): void
    {
        $post = Post::factory()->scheduled()->create();

        $this->get(route('posts.show', $post->slug))->assertNotFound();
    }

    public function test_published_post_with_nonexistent_author_renders_successfully_omitting_author_line(): void
    {
        DB::rollBack();
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::beginTransaction();

        try {
            $post = Post::factory()->create([
                'user_id' => 999999,
                'title' => 'Orphaned Post',
                'body' => 'Content without author.',
            ]);

            $this->get(route('posts.show', $post->slug))
                ->assertOk()
                ->assertSeeText('Orphaned Post')
                ->assertDontSeeText('By ')
                ->assertDontSeeText('Anonymous');
        } finally {
            DB::rollBack();
            DB::statement('PRAGMA foreign_keys = ON');
            DB::beginTransaction();
        }
    }

    public function test_unknown_slug_returns_not_found(): void
    {
        $this->get(route('posts.show', 'does-not-exist'))->assertNotFound();
    }
}
