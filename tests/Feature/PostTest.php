<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_scope_excludes_drafts_and_scheduled_posts(): void
    {
        $published = Post::factory()->create();
        Post::factory()->draft()->create();
        Post::factory()->scheduled()->create();

        $this->assertSame([$published->id], Post::published()->pluck('id')->all());
    }

    public function test_post_published_exactly_now_is_visible(): void
    {
        $this->freezeTime();
        $post = Post::factory()->create(['published_at' => now()]);

        $this->assertTrue(Post::published()->whereKey($post->id)->exists());
    }

    public function test_slug_is_generated_from_title_and_made_unique(): void
    {
        $first = Post::factory()->create(['title' => 'Hello World', 'slug' => null]);
        $second = Post::factory()->create(['title' => 'Hello World', 'slug' => null]);

        $this->assertSame('hello-world', $first->slug);
        $this->assertSame('hello-world-2', $second->slug);
    }

    public function test_reading_time_is_calculated_from_word_count(): void
    {
        $short = Post::factory()->make(['body' => 'Just a few words.']);
        $long = Post::factory()->make(['body' => implode(' ', array_fill(0, 450, 'word'))]);

        $this->assertSame(1, $short->reading_time);
        $this->assertSame(3, $long->reading_time);
    }

    public function test_body_html_renders_markdown_and_strips_raw_html(): void
    {
        $post = Post::factory()->make([
            'body' => "# Title\n\n```php\necho 1;\n```\n\n<script>alert(1)</script>",
        ]);

        $this->assertStringContainsString('<h1>Title</h1>', $post->body_html);
        $this->assertStringContainsString('<pre><code class="language-php">', $post->body_html);
        $this->assertStringNotContainsString('<script>', $post->body_html);
    }

    public function test_post_belongs_to_author_and_has_many_tags(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author, 'author')->hasAttached(Tag::factory()->count(2))->create();

        $this->assertTrue($post->author->is($author));
        $this->assertCount(2, $post->tags);
        $this->assertCount(1, $post->tags->first()->posts);
    }

    public function test_tag_slug_is_generated_from_name(): void
    {
        $tag = Tag::create(['name' => 'Web Development']);

        $this->assertSame('web-development', $tag->slug);
    }
}
