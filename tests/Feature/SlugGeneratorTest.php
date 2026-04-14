<?php

use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Services\SlugGenerator;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;

it('generates a slug from a string', function () {
    $slug = SlugGenerator::generate(new Page, 'My Page Title');

    expect($slug)->toBe('my-page-title');
});

it('returns the original slug when no conflict exists', function () {
    Page::factory()->create([
        'slug' => 'existing-page',
        'template' => TestTemplate::class,
    ]);

    $slug = SlugGenerator::generate(new Page, 'New Page');

    expect($slug)->toBe('new-page');
});

it('appends a number when slug already exists', function () {
    Page::factory()->create([
        'slug' => 'my-page',
        'template' => TestTemplate::class,
    ]);

    $slug = SlugGenerator::generate(new Page, 'My Page');

    expect($slug)->toBe('my-page-1');
});

it('increments the suffix for multiple conflicts', function () {
    Page::factory()->create(['slug' => 'my-page', 'template' => TestTemplate::class]);
    Page::factory()->create(['slug' => 'my-page-1', 'template' => TestTemplate::class]);
    Page::factory()->create(['slug' => 'my-page-2', 'template' => TestTemplate::class]);

    $slug = SlugGenerator::generate(new Page, 'My Page');

    expect($slug)->toBe('my-page-3');
});

it('excludes the current page when updating', function () {
    $page = Page::factory()->create(['slug' => 'my-page', 'template' => TestTemplate::class]);

    $slug = SlugGenerator::generate($page, 'My Page', $page->getKey());

    expect($slug)->toBe('my-page');
});

it('handles special characters in slug generation', function () {
    $slug = SlugGenerator::generate(new Page, 'Hello World! Special');

    expect($slug)->toBe('hello-world-special');
});

it('generates unique slugs for empty database', function () {
    Page::query()->delete();

    $slug = SlugGenerator::generate(new Page, 'First Page');

    expect($slug)->toBe('first-page');
});
