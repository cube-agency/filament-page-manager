<?php

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\CreatePage;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;
use Livewire\Livewire;
use function Pest\Laravel\assertDatabaseHas;

it('can open create form', function () {
    Livewire::withQueryParams(['template' => TestTemplate::class])
        ->test(CreatePage::class)
        ->assertStatus(200);
});

it('can create a page', function () {
    $newPage = Page::factory()->make();

    Livewire::withQueryParams(['template' => TestTemplate::class])
        ->test(CreatePage::class)
        ->fillForm([
            'name' => $newPage->name,
            'slug' => $newPage->slug,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Page::class, [
        'name' => $newPage->name,
        'slug' => $newPage->slug,
        'template' => TestTemplate::class
    ]);
});
