<?php

use CubeAgency\FilamentPageManager\Services\PageRoutesCache;

afterEach(function () {
    $cachedRoutesPath = app()->getCachedRoutesPath();
    $timestampPath = $cachedRoutesPath . '.timestamp';
    $pagesLastUpdated = $cachedRoutesPath . '.pages_last_updated';

    if (file_exists($timestampPath)) {
        @unlink($timestampPath);
    }
    if (file_exists($pagesLastUpdated)) {
        @unlink($pagesLastUpdated);
    }
});

it('reports no update when cache is current', function () {
    $now = time();
    $cachedRoutesPath = app()->getCachedRoutesPath() . '.timestamp';
    file_put_contents($cachedRoutesPath, $now);

    $this->artisan('filament-page-manager:route-cache', ['--json' => true])
        ->expectsOutputToContain('"updated":false')
        ->assertExitCode(0);
});

it('refreshes obsolete route cache with json output', function () {
    PageRoutesCache::setLastUpdateTimestamp(time());

    $this->artisan('filament-page-manager:route-cache', ['--json' => true])
        ->assertExitCode(0);
});

it('reports no output when cache is current and json is not set', function () {
    $now = time();
    $cachedRoutesPath = app()->getCachedRoutesPath() . '.timestamp';
    file_put_contents($cachedRoutesPath, $now);

    $this->artisan('filament-page-manager:route-cache')
        ->assertExitCode(0);
});
