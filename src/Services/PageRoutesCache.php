<?php

namespace CubeAgency\FilamentPageManager\Services;

use Illuminate\Support\Facades\Artisan;

class PageRoutesCache
{
    protected const CACHE_KEY = 'nodes.last_update';

    public static function cacheRoutes(): void
    {
        Artisan::call('route:cache');

        file_put_contents(self::getCachedRoutesTimestampPath(), self::getLatestNodeUpdateTimestamp());
    }

    public static function clearCache(): void
    {
        Artisan::call('route:clear');

        $cachedRoutesTimestampPath = self::getCachedRoutesTimestampPath();
        if (file_exists($cachedRoutesTimestampPath)) {
            unlink($cachedRoutesTimestampPath);
        }
    }

    public static function getCurrentCacheTimestamp(): ?int
    {
        $cachedRoutesTimestampPath = self::getCachedRoutesTimestampPath();

        if (!file_exists($cachedRoutesTimestampPath)) {
            return null;
        }

        return (int)file_get_contents($cachedRoutesTimestampPath);
    }

    public static function isRouteCacheObsolete(): bool
    {
        $currentCacheTimestamp = self::getCurrentCacheTimestamp();

        if (!$currentCacheTimestamp) {
            return true;
        }

        $lastModifiedTimestamp = self::getLatestNodeUpdateTimestamp();

        if (!$lastModifiedTimestamp) {
            return false;
        }

        return $lastModifiedTimestamp > $currentCacheTimestamp;
    }

    public static function getLatestNodeUpdateTimestamp(): ?int
    {
        $pagesLastUpdated = self::getPagesLastUpdatedTimestampPath();

        if (!file_exists($pagesLastUpdated)) {
            return null;
        }

        return (int)file_get_contents($pagesLastUpdated);
    }

    public static function setLastUpdateTimestamp(int $time): void
    {
        file_put_contents(self::getPagesLastUpdatedTimestampPath(), $time);
    }

    protected static function getCachedRoutesTimestampPath(): string
    {
        return app()->getCachedRoutesPath() . '.timestamp';
    }

    protected static function getPagesLastUpdatedTimestampPath(): string
    {
        return app()->getCachedRoutesPath() . '.pages_last_updated';
    }
}
