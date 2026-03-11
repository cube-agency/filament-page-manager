<?php

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\ListPages;
use CubeAgency\FilamentPageManager\FilamentPageManagerServiceProvider;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Services\PageRoutesCache;
use CubeAgency\FilamentPageManager\Tests\Fixtures\Templates\TestTemplate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Filesystem\Filesystem;
use Livewire\Livewire;

beforeEach(function () {
    $this->originalRoutesCachePath = getenv('APP_ROUTES_CACHE');
    $this->routesCacheDirectory = sys_get_temp_dir() . '/filament-page-manager-tests/' . bin2hex(random_bytes(8));

    app(Filesystem::class)->ensureDirectoryExists($this->routesCacheDirectory);

    setRoutesCachePath($this->routesCacheDirectory . '/routes.php');
});

afterEach(function () {
    app(Filesystem::class)->deleteDirectory($this->routesCacheDirectory);

    if ($this->originalRoutesCachePath === false) {
        unsetRoutesCachePath();

        return;
    }

    setRoutesCachePath($this->originalRoutesCachePath);
});

it('does not delete the active route cache file when purging obsolete metadata', function () {
    $routesCachePath = app()->getCachedRoutesPath();

    file_put_contents($routesCachePath, '<?php return [];');
    file_put_contents($routesCachePath . '.timestamp', '10');
    PageRoutesCache::setLastUpdateTimestamp(20);

    $provider = new class(app()) extends FilamentPageManagerServiceProvider
    {
        public function purgeForTest(): void
        {
            $this->purgeOutdatedRouteCache();
        }
    };

    $provider->purgeForTest();

    expect(file_exists($routesCachePath))->toBeTrue()
        ->and(file_exists($routesCachePath . '.timestamp'))->toBeFalse()
        ->and(PageRoutesCache::getLatestNodeUpdateTimestamp())->toBe(20);
});

it('rebuilds the route cache and records the latest page update timestamp', function () {
    Page::factory()->create([
        'template' => TestTemplate::class,
    ]);

    PageRoutesCache::setLastUpdateTimestamp(123456);

    expect(PageRoutesCache::cacheRoutes())->toBeTrue()
        ->and(file_exists(app()->getCachedRoutesPath()))->toBeTrue()
        ->and(file_get_contents(app()->getCachedRoutesPath()))->toContain('setCompiledRoutes')
        ->and(PageRoutesCache::getCurrentCacheTimestamp())->toBe(123456)
        ->and(PageRoutesCache::cacheRoutes())->toBeFalse();
});

it('skips rebuilding when another process already holds the route cache lock', function () {
    $lockHandle = fopen(app()->getCachedRoutesPath() . '.lock', 'c+');

    expect($lockHandle)->not->toBeFalse();

    PageRoutesCache::setLastUpdateTimestamp(999999);

    try {
        expect(flock($lockHandle, LOCK_EX | LOCK_NB))->toBeTrue()
            ->and(PageRoutesCache::cacheRoutes())->toBeFalse()
            ->and(file_exists(app()->getCachedRoutesPath()))->toBeFalse();
    } finally {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }
});

it('schedules route cache refreshes without overlapping', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command, 'filament-page-manager:route-cache'));

    expect($event)->not->toBeNull()
        ->and($event->withoutOverlapping)->toBeTrue();
});

it('marks the route cache as stale when sorting pages', function () {
    $firstPage = Page::factory()->create([
        'template' => TestTemplate::class,
    ]);

    $secondPage = Page::factory()->create([
        'template' => TestTemplate::class,
    ]);

    PageRoutesCache::setLastUpdateTimestamp(1);

    Livewire::test(ListPages::class)
        ->call('sortRows', [
            ['id' => $secondPage->getKey()],
            ['id' => $firstPage->getKey()],
        ]);

    expect(PageRoutesCache::getLatestNodeUpdateTimestamp())->toBeGreaterThan(1);
});

function setRoutesCachePath(string $path): void
{
    putenv("APP_ROUTES_CACHE={$path}");
    $_ENV['APP_ROUTES_CACHE'] = $path;
    $_SERVER['APP_ROUTES_CACHE'] = $path;
}

function unsetRoutesCachePath(): void
{
    putenv('APP_ROUTES_CACHE');
    unset($_ENV['APP_ROUTES_CACHE'], $_SERVER['APP_ROUTES_CACHE']);
}