<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = config('blog.media_disk') ?: 'public';
        Storage::fake($this->disk);
    }

    public function test_guest_is_redirected_from_profile_page(): void
    {
        $this->get('/admin/profile')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_access_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/profile')
            ->assertOk();
    }

    public function test_admin_can_upload_avatar_and_it_is_stored_as_webp(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('me.png', 200, 200);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'avatar_path' => $file,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        $this->assertStringStartsWith('avatars/', $user->avatar_path);
        $this->assertStringEndsWith('.webp', $user->avatar_path);
        Storage::disk($this->disk)->assertExists($user->avatar_path);

        $contents = Storage::disk($this->disk)->get($user->avatar_path);
        $this->assertStringStartsWith('RIFF', $contents);
        $this->assertSame('WEBP', substr($contents, 8, 4));
    }

    public function test_replacing_avatar_prunes_previous_avatar_file(): void
    {
        $oldPath = 'avatars/original-photo.webp';
        Storage::disk($this->disk)->put($oldPath, 'sample-old-photo-content');

        $user = User::factory()->withAvatar($oldPath)->create();
        $this->actingAs($user);

        Storage::disk($this->disk)->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('replacement.jpg', 150, 150);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'avatar_path' => $newFile,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertNotSame($oldPath, $user->avatar_path);
        Storage::disk($this->disk)->assertMissing($oldPath);
        Storage::disk($this->disk)->assertExists($user->avatar_path);
    }

    public function test_removing_avatar_prunes_file_and_clears_avatar_path(): void
    {
        $oldPath = 'avatars/photo-to-delete.webp';
        Storage::disk($this->disk)->put($oldPath, 'sample-photo-content');

        $user = User::factory()->withAvatar($oldPath)->create();
        $this->actingAs($user);

        Storage::disk($this->disk)->assertExists($oldPath);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'avatar_path' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertNull($user->avatar_path);
        Storage::disk($this->disk)->assertMissing($oldPath);
    }

    public function test_topbar_renders_custom_avatar_when_assigned(): void
    {
        $path = 'avatars/my-avatar.webp';
        Storage::disk($this->disk)->put($path, 'sample-avatar');

        $user = User::factory()->withAvatar($path)->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee($user->avatar_url, false);
    }

    public function test_topbar_renders_fallback_when_avatar_is_null(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Admin',
            'avatar_path' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin')
            ->assertOk();

        // The custom avatar disk URL should not appear
        $response->assertDontSee('/storage/avatars/');
    }

    public function test_avatar_upload_fails_when_exceeding_5mb(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 6000 KB is greater than the 5120 KB limit
        $oversizedFile = UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg');

        Livewire::test(EditProfile::class)
            ->fillForm([
                'avatar_path' => $oversizedFile,
            ])
            ->call('save')
            ->assertHasFormErrors(['avatar_path']);
    }

    public function test_avatar_upload_fails_for_disallowed_mime_types(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        Livewire::test(EditProfile::class)
            ->fillForm([
                'avatar_path' => $pdfFile,
            ])
            ->call('save')
            ->assertHasFormErrors(['avatar_path']);
    }
}
