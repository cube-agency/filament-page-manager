<?php

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\ListPages;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;
use Livewire\Livewire;

it('can view empty pages list', function () {
    Livewire::test(ListPages::class)
        ->assertStatus(200);
});

it('can view populated pages list', function () {
    $newPage = Page::factory()->create([
        'template' => TestTemplate::class
    ]);

    Livewire::test(ListPages::class)
        ->assertStatus(200)
        ->assertSee($newPage->name);
});

