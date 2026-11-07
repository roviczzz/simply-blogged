<?php

namespace Tests\Feature;

use App\Filament\Resources\Tags\Pages\ManageTags;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_page_shows_tags(): void
    {
        $tags = Tag::factory()->count(3)->create();

        Livewire::test(ManageTags::class)
            ->assertOk()
            ->assertCanSeeTableRecords($tags);
    }

    public function test_tag_can_be_created(): void
    {
        Livewire::test(ManageTags::class)
            ->callAction('create', ['name' => 'Laravel', 'slug' => 'laravel'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tags', ['name' => 'Laravel', 'slug' => 'laravel']);
    }

    public function test_tag_can_be_updated(): void
    {
        $tag = Tag::factory()->create(['name' => 'Old', 'slug' => 'old']);

        Livewire::test(ManageTags::class)
            ->callAction(TestAction::make('edit')->table($tag), ['name' => 'New', 'slug' => 'new'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'New', 'slug' => 'new']);
    }

    public function test_tag_can_be_deleted(): void
    {
        $tag = Tag::factory()->create();

        Livewire::test(ManageTags::class)
            ->callAction(TestAction::make('delete')->table($tag));

        $this->assertModelMissing($tag);
    }

    public function test_tag_slug_must_be_unique(): void
    {
        Tag::factory()->create(['slug' => 'taken']);

        Livewire::test(ManageTags::class)
            ->callAction('create', ['name' => 'Other', 'slug' => 'taken'])
            ->assertHasFormErrors(['slug' => 'unique']);
    }
}
