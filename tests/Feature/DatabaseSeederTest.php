<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_in_production_seeds_admin_and_excludes_mock_content(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'blog.admin.email' => 'admin@production.com',
            'blog.admin.name' => 'Prod Admin',
            'blog.admin.password' => 'ProductionSecurePassword123!',
        ]);

        $this->artisan('db:seed', ['--force' => true]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@production.com',
            'name' => 'Prod Admin',
        ]);
        $this->assertSame(1, User::count());
        $this->assertDatabaseCount('posts', 0);
        $this->assertDatabaseCount('tags', 0);
        $this->assertSame(0, Post::count());
        $this->assertSame(0, Tag::count());
    }

    public function test_database_seeder_in_non_production_seeds_admin_and_mock_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => config('blog.admin.email'),
            'name' => config('blog.admin.name'),
        ]);
        $this->assertGreaterThan(0, Post::count());
        $this->assertGreaterThan(0, Tag::count());
    }
}
