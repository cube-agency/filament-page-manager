<?php

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\ListPages;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;
use Livewire\Livewire;
use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing};

it('can delete a page', function () {
    $newPage = Page::factory()->create([
        'template' => TestTemplate::class
    ]);

    assertDatabaseHas(Page::class, [
        'name' => $newPage->name
    ]);

    Livewire::test(ListPages::class)
        ->callAction('delete', [], ['row' => $newPage->getKey()]);

    assertDatabaseMissing(Page::class, [
        'name' => $newPage->name
    ]);
});
