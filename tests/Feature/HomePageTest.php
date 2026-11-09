<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_lists_published_posts_with_details(): void
    {
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        $post = Post::factory()->hasAttached($tag)->create([
            'title' => 'A Visible Post',
            'excerpt' => 'Visible excerpt text.',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('A Visible Post')
            ->assertSeeText('Visible excerpt text.')
            ->assertSeeText('1 min read')
            ->assertSeeText('Laravel')
            ->assertSee(route('posts.show', $post->slug), false)
            ->assertSee(route('tags.show', $tag->slug), false);
    }

    public function test_homepage_hides_draft_and_scheduled_posts(): void
    {
        Post::factory()->draft()->create(['title' => 'Secret Draft']);
        Post::factory()->scheduled()->create(['title' => 'Future Post']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSeeText('Secret Draft')
            ->assertDontSeeText('Future Post')
            ->assertSeeText('No posts have been published yet.');
    }

    public function test_homepage_orders_posts_by_publication_date_descending(): void
    {
        Post::factory()->create(['title' => 'Older Post', 'published_at' => now()->subDays(5)]);
        Post::factory()->create(['title' => 'Newer Post', 'published_at' => now()->subDay()]);

        $this->get(route('home'))->assertSeeInOrder(['Newer Post', 'Older Post']);
    }

    public function test_homepage_paginates_posts(): void
    {
        Post::factory()->count(12)->sequence(fn ($sequence) => [
            'title' => sprintf('Entry %02d', $sequence->index + 1),
            'published_at' => now()->subDays(12 - $sequence->index),
        ])->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Entry 12')
            ->assertSeeText('Entry 03')
            ->assertDontSeeText('Entry 02')
            ->assertDontSeeText('Entry 01')
            ->assertSee('page=2', false);

        $this->get(route('home', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('Entry 02')
            ->assertSeeText('Entry 01')
            ->assertDontSeeText('Entry 03');
    }

    public function test_layout_includes_seo_meta_and_feed_link(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('property="og:site_name"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/rss+xml', false);
    }

    public function test_layout_includes_brand_logo_and_favicon(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('rel="icon" type="image/x-icon" href="'.asset('favicon.ico').'"', false)
            ->assertSee('<img src="'.asset('simply-blogged.webp').'" alt="" class="h-8 w-8 rounded-lg object-contain">', false)
            ->assertSee('<span class="text-xl font-semibold tracking-tight">'.config('app.name').'</span>', false);
    }

    public function test_header_renders_sign_in_link_for_guests(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('filament.admin.auth.login').'"', false)
            ->assertSeeText('Sign in')
            ->assertDontSeeText('Admin')
            ->assertDontSeeText('Sign out');
    }

    public function test_header_renders_admin_and_sign_out_links_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('filament.admin.pages.dashboard').'"', false)
            ->assertSeeText('Admin')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSeeText('Sign out')
            ->assertDontSeeText('Sign in');
    }
}
