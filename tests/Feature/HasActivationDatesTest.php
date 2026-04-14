<?php

use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;

it('parses activate_at as Carbon instance', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => '2025-01-15 10:30:00',
    ]);

    $page->refresh();

    expect($page->activate_at)->toBeInstanceOf(\Carbon\Carbon::class)
        ->and($page->activate_at->format('Y-m-d H:i:s'))->toBe('2025-01-15 10:30:00');
});

it('parses expire_at as Carbon instance', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'expire_at' => '2025-12-31 23:59:59',
    ]);

    $page->refresh();

    expect($page->expire_at)->toBeInstanceOf(\Carbon\Carbon::class)
        ->and($page->expire_at->format('Y-m-d H:i:s'))->toBe('2025-12-31 23:59:59');
});

it('returns null for unset activate_at', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => null,
    ]);

    expect($page->activate_at)->toBeNull();
});

it('returns null for unset expire_at', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'expire_at' => null,
    ]);

    expect($page->expire_at)->toBeNull();
});

it('detects an active page', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => now()->subHour(),
        'expire_at' => now()->addHour(),
    ]);

    $page->refresh();

    expect($page->active)->toBeTrue();
});

it('detects a page that has not yet activated', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => now()->addHour(),
        'expire_at' => null,
    ]);

    $page->refresh();

    expect($page->active)->toBeFalse();
});

it('detects an expired page', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => now()->subHours(2),
        'expire_at' => now()->subHour(),
    ]);

    $page->refresh();

    expect($page->active)->toBeFalse();
});

it('detects a page with no expiry as not expired', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => now()->subHour(),
        'expire_at' => null,
    ]);

    expect($page->hasExpired())->toBeFalse();
});

it('detects a page that has activated', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => now()->subHour(),
    ]);

    expect($page->hasActivated())->toBeTrue();
});

it('detects a page that has not activated when activate_at is null', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'activate_at' => null,
    ]);

    expect($page->hasActivated())->toBeFalse();
});

it('scopes active pages only', function () {
    Page::factory()->create([
        'name' => 'active-page',
        'template' => TestTemplate::class,
        'activate_at' => now()->subHour(),
        'expire_at' => now()->addHour(),
    ]);

    Page::factory()->create([
        'name' => 'future-page',
        'template' => TestTemplate::class,
        'activate_at' => now()->addHour(),
        'expire_at' => null,
    ]);

    Page::factory()->create([
        'name' => 'expired-page',
        'template' => TestTemplate::class,
        'activate_at' => now()->subHours(2),
        'expire_at' => now()->subHour(),
    ]);

    $activePages = Page::active()->get();

    expect($activePages)->toHaveCount(1)
        ->and($activePages->first()->name)->toBe('active-page');
});

it('scopes active pages with no expiry', function () {
    Page::factory()->create([
        'name' => 'no-expiry-active',
        'template' => TestTemplate::class,
        'activate_at' => now()->subHour(),
        'expire_at' => null,
    ]);

    $activePages = Page::active()->get();

    expect($activePages)->toHaveCount(1)
        ->and($activePages->first()->name)->toBe('no-expiry-active');
});
