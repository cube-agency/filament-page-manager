<?php

use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Models\PagePreview;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;

it('clears expired page previews', function () {
    $page = Page::factory()->create(['template' => TestTemplate::class]);

    PagePreview::create([
        'page_id' => $page->getKey(),
        'data' => ['name' => 'Old Preview'],
        'token' => 'expired-token-1',
        'expires_at' => now()->subHour(),
    ]);

    PagePreview::create([
        'page_id' => $page->getKey(),
        'data' => ['name' => 'Valid Preview'],
        'token' => 'valid-token-1',
        'expires_at' => now()->addHour(),
    ]);

    $this->artisan('filament-page-manager:clear-page-previews')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('filament_page_previews', ['token' => 'expired-token-1']);
    $this->assertDatabaseHas('filament_page_previews', ['token' => 'valid-token-1']);
});

it('keeps non-expired page previews', function () {
    $page = Page::factory()->create(['template' => TestTemplate::class]);

    PagePreview::create([
        'page_id' => $page->getKey(),
        'data' => ['name' => 'Future Preview'],
        'token' => 'future-token',
        'expires_at' => now()->addHours(2),
    ]);

    $this->artisan('filament-page-manager:clear-page-previews')
        ->assertExitCode(0);

    $this->assertDatabaseHas('filament_page_previews', ['token' => 'future-token']);
});

it('handles empty previews table', function () {
    $this->artisan('filament-page-manager:clear-page-previews')
        ->expectsOutput('Page previews cleared.')
        ->assertExitCode(0);
});

it('clears previews at exact expiry time', function () {
    $page = Page::factory()->create(['template' => TestTemplate::class]);

    PagePreview::create([
        'page_id' => $page->getKey(),
        'data' => ['name' => 'Just Expired'],
        'token' => 'just-expired-token',
        'expires_at' => now()->subSecond(),
    ]);

    $this->artisan('filament-page-manager:clear-page-previews')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('filament_page_previews', ['token' => 'just-expired-token']);
});
