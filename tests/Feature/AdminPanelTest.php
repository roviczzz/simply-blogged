<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_login_page_renders_brand_logo_and_favicon(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('simply-blogged.webp')
            ->assertSee('favicon.ico');
    }

    public function test_admin_dashboard_renders_brand_logo_and_favicon(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('simply-blogged.webp')
            ->assertSee('favicon.ico');
    }
}
