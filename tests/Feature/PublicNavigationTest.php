<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_views_header_navigation_without_avatar(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sign in')
            ->assertDontSee('Admin')
            ->assertDontSee('/storage/avatars/');
    }

    public function test_authenticated_admin_with_avatar_renders_avatar_beside_admin_link(): void
    {
        $user = User::factory()->withAvatar()->create([
            'name' => 'Admin Boss',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Admin')
            ->assertSee('Sign out')
            ->assertSee($user->avatar_url, false)
            ->assertSee('alt="Admin Boss"', false);
    }

    public function test_authenticated_admin_without_avatar_renders_admin_link_without_image(): void
    {
        $user = User::factory()->create([
            'name' => 'Plain Admin',
            'avatar_path' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Admin')
            ->assertSee('Sign out');

        $response->assertDontSee('/storage/avatars/');
        $response->assertDontSee('alt="Plain Admin"', false);
    }
}
