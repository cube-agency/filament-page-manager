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

it('returns null when no cache timestamp exists', function () {
    $timestamp = PageRoutesCache::getCurrentCacheTimestamp();

    expect($timestamp)->toBeNull();
});

it('returns null when no node update timestamp exists', function () {
    $timestamp = PageRoutesCache::getLatestNodeUpdateTimestamp();

    expect($timestamp)->toBeNull();
});

it('considers cache obsolete when no cache timestamp exists', function () {
    expect(PageRoutesCache::isRouteCacheObsolete())->toBeTrue();
});

it('sets and retrieves the last update timestamp', function () {
    $time = time();
    PageRoutesCache::setLastUpdateTimestamp($time);

    $timestamp = PageRoutesCache::getLatestNodeUpdateTimestamp();

    expect($timestamp)->toBe($time);
});

it('does not consider cache obsolete when no updates have occurred', function () {
    $cachedRoutesPath = app()->getCachedRoutesPath() . '.timestamp';
    file_put_contents($cachedRoutesPath, time());

    expect(PageRoutesCache::isRouteCacheObsolete())->toBeFalse();
});

it('considers cache obsolete when update is newer than cache', function () {
    $now = time();

    $cachedRoutesPath = app()->getCachedRoutesPath() . '.timestamp';
    file_put_contents($cachedRoutesPath, $now - 100);

    PageRoutesCache::setLastUpdateTimestamp($now);

    expect(PageRoutesCache::isRouteCacheObsolete())->toBeTrue();
});

it('does not consider cache obsolete when cache is newer than update', function () {
    $now = time();

    $cachedRoutesPath = app()->getCachedRoutesPath() . '.timestamp';
    file_put_contents($cachedRoutesPath, $now);

    PageRoutesCache::setLastUpdateTimestamp($now - 100);

    expect(PageRoutesCache::isRouteCacheObsolete())->toBeFalse();
});

it('clears cache without error when no cache files exist', function () {
    expect(fn () => PageRoutesCache::clearCache())->not->toThrow(Exception::class);
});
