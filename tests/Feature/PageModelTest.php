<?php

use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;

it('identifies a root page', function () {
    $page = Page::factory()->create([
        'parent_id' => null,
        'template' => TestTemplate::class,
    ]);

    expect($page->isChild())->toBeFalse();
});

it('identifies a child page', function () {
    $parent = Page::factory()->create([
        'template' => TestTemplate::class,
    ]);

    $child = Page::factory()->create([
        'parent_id' => $parent->getKey(),
        'template' => TestTemplate::class,
    ]);

    expect($child->isChild())->toBeTrue();
});

it('returns the slug as uri for a root page', function () {
    $page = Page::factory()->create([
        'parent_id' => null,
        'slug' => 'about-us',
        'template' => TestTemplate::class,
    ]);

    expect($page->getUri())->toBe('about-us');
});

it('returns uri with parent slugs for a child page', function () {
    $parent = Page::factory()->create([
        'parent_id' => null,
        'slug' => 'services',
        'template' => TestTemplate::class,
    ]);

    $child = Page::factory()->create([
        'parent_id' => $parent->getKey(),
        'slug' => 'web-design',
        'template' => TestTemplate::class,
    ]);

    expect($child->getUri())->toBe('services/web-design');
});

it('returns uri without self when withThis is false', function () {
    $parent = Page::factory()->create([
        'parent_id' => null,
        'slug' => 'services',
        'template' => TestTemplate::class,
    ]);

    $child = Page::factory()->create([
        'parent_id' => $parent->getKey(),
        'slug' => 'web-design',
        'template' => TestTemplate::class,
    ]);

    expect($child->getUri(withThis: false))->toBe('services');
});

it('returns full url for a root page', function () {
    $page = Page::factory()->create([
        'parent_id' => null,
        'slug' => 'about-us',
        'template' => TestTemplate::class,
    ]);

    expect($page->getFullUrl())->toBe(config('app.url') . '/about-us/');
});

it('returns full url for a child page', function () {
    $parent = Page::factory()->create([
        'parent_id' => null,
        'slug' => 'services',
        'template' => TestTemplate::class,
    ]);

    $child = Page::factory()->create([
        'parent_id' => $parent->getKey(),
        'slug' => 'web-design',
        'template' => TestTemplate::class,
    ]);

    expect($child->getFullUrl())->toBe(config('app.url') . '/services/web-design/');
});

it('returns full url without self when withThis is false', function () {
    $parent = Page::factory()->create([
        'parent_id' => null,
        'slug' => 'services',
        'template' => TestTemplate::class,
    ]);

    $child = Page::factory()->create([
        'parent_id' => $parent->getKey(),
        'slug' => 'web-design',
        'template' => TestTemplate::class,
    ]);

    expect($child->getFullUrl(withThis: false))->toBe(config('app.url') . '/services/');
});

it('builds route name with default action', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
    ]);

    $routeName = $page->getRouteName();

    expect($routeName)->toBe(config('filament-page-manager.route_name_prefix') . '.' . $page->getKey() . '.index');
});

it('builds route name with custom action', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
    ]);

    $routeName = $page->getRouteName('show');

    expect($routeName)->toBe(config('filament-page-manager.route_name_prefix') . '.' . $page->getKey() . '.show');
});

it('returns empty string for url when route does not exist', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
    ]);

    $url = $page->getUrl();

    expect($url)->toBe('');
});

it('casts content to array', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'content' => ['key' => 'value'],
    ]);

    $page->refresh();

    expect($page->content)->toBeArray()->toHaveKey('key', 'value');
});

it('casts meta to array', function () {
    $page = Page::factory()->create([
        'template' => TestTemplate::class,
        'meta' => ['title' => 'Page Title'],
    ]);

    $page->refresh();

    expect($page->meta)->toBeArray()->toHaveKey('title', 'Page Title');
});
