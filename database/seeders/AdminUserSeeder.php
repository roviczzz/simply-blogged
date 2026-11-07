<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = config('blog.admin.password');

        if (app()->isProduction() && (empty($password) || trim((string) $password) === '' || $password === 'password')) {
            throw new RuntimeException('Cannot seed default or empty admin password in production. Please set a secure ADMIN_PASSWORD in your environment.');
        }

        User::firstOrCreate(
            ['email' => config('blog.admin.email')],
            [
                'name' => config('blog.admin.name'),
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
    }
}
