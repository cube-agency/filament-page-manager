<?php

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\ListPages;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;
use Livewire\Livewire;
use function Pest\Laravel\assertDatabaseHas;

it('can clone a page', function () {
    $newPage = Page::factory()->create([
        'template' => TestTemplate::class
    ]);

    Livewire::test(ListPages::class)
        ->callAction('clone', [], ['row' => $newPage->getKey()]);

    assertDatabaseHas(Page::class, [
        'name' => $newPage->name,
        'slug' => $newPage->slug . '-1',
    ]);
});
