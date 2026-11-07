<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_seeded_with_default_credentials_in_testing(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => config('blog.admin.email'),
            'name' => config('blog.admin.name'),
        ]);

        $user = User::where('email', config('blog.admin.email'))->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check(config('blog.admin.password'), $user->password));
    }

    public function test_seeding_in_production_with_default_password_throws_exception(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['blog.admin.password' => 'password']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot seed default or empty admin password in production');

        $this->artisan('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);
    }

    public function test_seeding_in_production_with_empty_password_throws_exception(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['blog.admin.password' => '']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot seed default or empty admin password in production');

        $this->artisan('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);
    }

    public function test_seeding_in_production_with_custom_password_succeeds(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'blog.admin.email' => 'prod-admin@example.com',
            'blog.admin.name' => 'Production Admin',
            'blog.admin.password' => 'SuperSecretCustomPassword123!',
        ]);

        $this->artisan('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);

        $this->assertDatabaseHas('users', [
            'email' => 'prod-admin@example.com',
            'name' => 'Production Admin',
        ]);

        $user = User::where('email', 'prod-admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('SuperSecretCustomPassword123!', $user->password));
    }

    public function test_admin_seeding_is_idempotent(): void
    {
        $this->seed(AdminUserSeeder::class);
        $this->assertSame(1, User::count());

        $admin = User::first();
        $admin->update(['name' => 'Updated Admin Name']);

        // Running seeder again should not duplicate user or overwrite modified fields
        $this->seed(AdminUserSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame('Updated Admin Name', $admin->fresh()->name);
    }
}
