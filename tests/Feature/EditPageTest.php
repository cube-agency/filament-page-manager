<?php

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\EditPage;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;
use Livewire\Livewire;
use function Pest\Laravel\assertDatabaseHas;

it('can open edit form', function () {
    $newPage = Page::factory()->create([
        'template' => TestTemplate::class
    ]);

    Livewire::test(EditPage::class, [$newPage->id])
        ->assertStatus(200);
});

it('can update a page', function () {
    $newPage = Page::factory()->create([
        'template' => TestTemplate::class
    ]);

    Livewire::test(EditPage::class, [$newPage->id])
        ->fillForm([
            'name' => 'Updated',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Page::class, [
        'name' => 'Updated',
        'slug' => 'updated',
    ]);
});
