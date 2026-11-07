<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_returns_valid_rss_document_with_post_items(): void
    {
        $post = Post::factory()->create([
            'title' => 'Feed & Friends',
            'excerpt' => 'An <excerpt> for the feed.',
        ]);

        $response = $this->get(route('feed'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml);
        $this->assertSame('rss', $xml->getName());
        $this->assertSame('2.0', (string) $xml['version']);
        $this->assertCount(1, $xml->channel->item);

        $item = $xml->channel->item[0];
        $this->assertSame('Feed & Friends', (string) $item->title);
        $this->assertSame(route('posts.show', $post->slug), (string) $item->link);
        $this->assertSame(route('posts.show', $post->slug), (string) $item->guid);
        $this->assertSame($post->published_at->toRssString(), (string) $item->pubDate);
        $this->assertSame('An <excerpt> for the feed.', (string) $item->description);
    }

    public function test_feed_excludes_draft_and_scheduled_posts_and_orders_newest_first(): void
    {
        Post::factory()->create(['title' => 'Older Item', 'published_at' => now()->subDays(3)]);
        Post::factory()->create(['title' => 'Newer Item', 'published_at' => now()->subDay()]);
        Post::factory()->draft()->create(['title' => 'Draft Item']);
        Post::factory()->scheduled()->create(['title' => 'Scheduled Item']);

        $xml = simplexml_load_string($this->get(route('feed'))->getContent());

        $titles = array_map('strval', $xml->xpath('/rss/channel/item/title'));

        $this->assertSame(['Newer Item', 'Older Item'], $titles);
    }

    public function test_feed_is_valid_with_no_posts(): void
    {
        $xml = simplexml_load_string($this->get(route('feed'))->assertOk()->getContent());

        $this->assertNotFalse($xml);
        $this->assertCount(0, $xml->channel->item);
    }
}
