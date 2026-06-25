<?php

use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Services\PageRoutes;

it('does not throw when registering routes without matching pages', function () {
    PageRoutes::for('non-existent-template', fn () => 'test');

    expect(fn () => PageRoutes::register())->not->toThrow(Exception::class);
});

it('does not throw when no routes are registered', function () {
    expect(fn () => PageRoutes::register())->not->toThrow(Exception::class);
});

it('registers routes for active pages', function () {
    $page = Page::factory()->create([
        'name' => 'Test Page',
        'slug' => 'test-page',
        'template' => 'SomeTemplate',
        'activate_at' => now()->subHour(),
        'expire_at' => null,
    ]);

    $registered = false;
    PageRoutes::for('SomeTemplate', function () use (&$registered) {
        $registered = true;
    });

    PageRoutes::register();

    expect($registered)->toBeTrue();
});

it('does not register routes for inactive pages', function () {
    Page::factory()->create([
        'name' => 'Future Page',
        'slug' => 'future-page',
        'template' => 'FutureTemplate',
        'activate_at' => now()->addHour(),
        'expire_at' => null,
    ]);

    $registered = false;
    PageRoutes::for('FutureTemplate', function () use (&$registered) {
        $registered = true;
    });

    PageRoutes::register();

    expect($registered)->toBeFalse();
});

it('does not register routes for expired pages', function () {
    Page::factory()->create([
        'name' => 'Expired Page',
        'slug' => 'expired-page',
        'template' => 'ExpiredTemplate',
        'activate_at' => now()->subHours(2),
        'expire_at' => now()->subHour(),
    ]);

    $registered = false;
    PageRoutes::for('ExpiredTemplate', function () use (&$registered) {
        $registered = true;
    });

    PageRoutes::register();

    expect($registered)->toBeFalse();
});
