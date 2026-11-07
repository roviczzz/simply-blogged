<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => config('blog.admin.email')],
            [
                'name' => config('blog.admin.name'),
                'password' => config('blog.admin.password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
