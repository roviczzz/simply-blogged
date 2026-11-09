<?php

namespace Tests\Unit;

use App\Models\User;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('blog.media_disk') ?: 'public');
    }

    public function test_user_implements_has_avatar_contract(): void
    {
        $user = new User;

        $this->assertInstanceOf(HasAvatar::class, $user);
    }

    public function test_user_without_avatar_returns_null_for_avatar_url_and_filament_avatar_url(): void
    {
        $user = User::factory()->create([
            'avatar_path' => null,
        ]);

        $this->assertNull($user->avatar_path);
        $this->assertNull($user->avatar_url);
        $this->assertNull($user->getFilamentAvatarUrl());
    }

    public function test_user_with_avatar_returns_expected_storage_url(): void
    {
        $disk = config('blog.media_disk') ?: 'public';
        $user = User::factory()->withAvatar()->create();

        $this->assertNotNull($user->avatar_path);
        $this->assertStringStartsWith('avatars/', $user->avatar_path);
        $this->assertStringEndsWith('.webp', $user->avatar_path);

        $expectedUrl = Storage::disk($disk)->url($user->avatar_path);

        $this->assertSame($expectedUrl, $user->avatar_url);
        $this->assertSame($expectedUrl, $user->getFilamentAvatarUrl());
    }

    public function test_user_with_avatar_state_accepts_custom_path(): void
    {
        $disk = config('blog.media_disk') ?: 'public';
        $customPath = 'avatars/custom-profile.webp';
        $user = User::factory()->withAvatar($customPath)->create();

        $this->assertSame($customPath, $user->avatar_path);
        $this->assertSame(Storage::disk($disk)->url($customPath), $user->avatar_url);
        $this->assertSame(Storage::disk($disk)->url($customPath), $user->getFilamentAvatarUrl());
    }
}
