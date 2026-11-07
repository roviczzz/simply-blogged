<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_page_lists_only_published_posts_with_that_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        $other = Tag::factory()->create(['name' => 'Other']);

        Post::factory()->hasAttached($tag)->create(['title' => 'Tagged Published']);
        Post::factory()->hasAttached($other)->create(['title' => 'Different Tag']);
        Post::factory()->draft()->hasAttached($tag)->create(['title' => 'Tagged Draft']);
        Post::factory()->scheduled()->hasAttached($tag)->create(['title' => 'Tagged Scheduled']);

        $this->get(route('tags.show', $tag->slug))
            ->assertOk()
            ->assertSeeText('Laravel')
            ->assertSeeText('Tagged Published')
            ->assertDontSeeText('Different Tag')
            ->assertDontSeeText('Tagged Draft')
            ->assertDontSeeText('Tagged Scheduled');
    }

    public function test_tag_without_published_posts_shows_empty_state(): void
    {
        $tag = Tag::factory()->create();

        $this->get(route('tags.show', $tag->slug))
            ->assertOk()
            ->assertSeeText('No published posts use this tag yet.');
    }

    public function test_unknown_tag_returns_not_found(): void
    {
        $this->get(route('tags.show', 'missing'))->assertNotFound();
    }
}
