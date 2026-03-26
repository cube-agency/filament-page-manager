<?php

namespace CubeAgency\FilamentPageManager\Services;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\RouteCacheCommand as LaravelRouteCacheCommand;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Artisan;
use ReflectionClass;

class PageRoutesCache
{
    public static function cacheRoutes(): bool
    {
        $lockHandle = self::acquireLock();

        if ($lockHandle === false) {
            return false;
        }

        try {
            if (! self::isRouteCacheObsolete()) {
                return false;
            }

            $routes = self::getFreshApplicationRoutes();

            if (count($routes) === 0) {
                return false;
            }

            foreach ($routes as $route) {
                $route->prepareForSerialization();
            }

            self::writeFile(
                app()->getCachedRoutesPath(),
                self::buildRouteCacheFile($routes),
            );

            self::writeFile(
                self::getCachedRoutesTimestampPath(),
                (string) self::resolveCacheTimestamp(),
            );

            return true;
        } finally {
            self::releaseLock($lockHandle);
        }
    }

    public static function clearCacheTimestamp(): void
    {
        app(Filesystem::class)->delete(self::getCachedRoutesTimestampPath());
    }

    public static function clearCache(): void
    {
        Artisan::call('route:clear');

        self::clearCacheTimestamp();
    }

    public static function getCurrentCacheTimestamp(): ?int
    {
        $cachedRoutesTimestampPath = self::getCachedRoutesTimestampPath();
        clearstatcache(true, $cachedRoutesTimestampPath);

        if (! file_exists($cachedRoutesTimestampPath)) {
            return null;
        }

        $contents = trim((string) file_get_contents($cachedRoutesTimestampPath));

        return $contents === '' ? null : (int) $contents;
    }

    public static function isRouteCacheObsolete(): bool
    {
        $currentCacheTimestamp = self::getCurrentCacheTimestamp();

        if (! $currentCacheTimestamp) {
            return true;
        }

        $lastModifiedTimestamp = self::getLatestNodeUpdateTimestamp();

        if (! $lastModifiedTimestamp) {
            return false;
        }

        return $lastModifiedTimestamp > $currentCacheTimestamp;
    }

    public static function getLatestNodeUpdateTimestamp(): ?int
    {
        $pagesLastUpdated = self::getPagesLastUpdatedTimestampPath();
        clearstatcache(true, $pagesLastUpdated);

        if (! file_exists($pagesLastUpdated)) {
            return null;
        }

        $contents = trim((string) file_get_contents($pagesLastUpdated));

        return $contents === '' ? null : (int) $contents;
    }

    public static function setLastUpdateTimestamp(int $time): void
    {
        self::writeFile(self::getPagesLastUpdatedTimestampPath(), (string) $time);
    }

    protected static function getCachedRoutesTimestampPath(): string
    {
        return app()->getCachedRoutesPath() . '.timestamp';
    }

    protected static function getPagesLastUpdatedTimestampPath(): string
    {
        return app()->getCachedRoutesPath() . '.pages_last_updated';
    }

    protected static function getLockPath(): string
    {
        return app()->getCachedRoutesPath() . '.lock';
    }

    /**
     * @return resource|false
     */
    protected static function acquireLock()
    {
        $lockPath = self::getLockPath();
        $directory = dirname($lockPath);

        @mkdir($directory, 0777, true);

        $handle = fopen($lockPath, 'c+');

        if ($handle === false) {
            return false;
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return false;
        }

        return $handle;
    }

    /**
     * @param resource|false $handle
     */
    protected static function releaseLock($handle): void
    {
        if ($handle === false) {
            return;
        }

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    protected static function writeFile(string $path, string $contents): void
    {
        $filesystem = app(Filesystem::class);

        $filesystem->ensureDirectoryExists(dirname($path));
        $filesystem->replace($path, $contents);
    }

    protected static function resolveCacheTimestamp(): int
    {
        return self::getLatestNodeUpdateTimestamp() ?? time();
    }

    protected static function getFreshApplicationRoutes(): RouteCollection
    {
        $temporaryRoutesCachePath = sprintf(
            '%s.%s.fresh',
            app()->getCachedRoutesPath(),
            bin2hex(random_bytes(8)),
        );

        return self::withRoutesCachePath($temporaryRoutesCachePath, function () {
            $app = require app()->bootstrapPath('app.php');
            $app->make(ConsoleKernelContract::class)->bootstrap();

            return tap($app['router']->getRoutes(), function (RouteCollection $routes) {
                $routes->refreshNameLookups();
                $routes->refreshActionLookups();
            });
        });
    }

    protected static function buildRouteCacheFile(RouteCollection $routes): string
    {
        $filesystem = app(Filesystem::class);
        $stubPath = dirname((new ReflectionClass(LaravelRouteCacheCommand::class))->getFileName()) . '/stubs/routes.stub';

        if (! $filesystem->exists($stubPath)) {
            throw new \RuntimeException("Laravel route cache stub not found at: {$stubPath}");
        }

        return str_replace('{{routes}}', var_export($routes->compile(), true), $filesystem->get($stubPath));
    }

    protected static function withRoutesCachePath(string $path, callable $callback): mixed
    {
        $originalValue = getenv('APP_ROUTES_CACHE');

        app(Filesystem::class)->delete($path);

        self::setEnvironmentValue('APP_ROUTES_CACHE', $path);

        try {
            return $callback();
        } finally {
            if ($originalValue === false) {
                self::unsetEnvironmentValue('APP_ROUTES_CACHE');
            } else {
                self::setEnvironmentValue('APP_ROUTES_CACHE', $originalValue);
            }

            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    protected static function setEnvironmentValue(string $key, string $value): void
    {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    protected static function unsetEnvironmentValue(string $key): void
    {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
}
