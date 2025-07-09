<?php

namespace CubeAgency\FilamentPageManager;

use CubeAgency\FilamentPageManager\Commands\RouteCacheCommand;
use CubeAgency\FilamentPageManager\Services\PageRoutes;
use CubeAgency\FilamentPageManager\Services\PageRoutesCache;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentPageManagerServiceProvider extends PackageServiceProvider
{
    use CanManipulateFiles;

    public static string $name = 'filament-page-manager';

    public static string $viewNamespace = 'filament-page-manager';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->startWith(function () {
                        $this->copyRoutesFile();
                    })
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('cube-agency/filament-page-manager');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        $this->registerRoutes();
        $this->registerPages();

        $this->purgeOutdatedRouteCache();
        $this->refreshObsoleteRouteCache();
    }

    protected function registerRoutes(): void
    {
        if (! config('filament-page-manager.register_routes', true)) {
            return;
        }

        $path = base_path('routes/pages.php');
        if (! File::exists($path)) {
            return;
        }

        $this->loadRoutesFrom($path);
    }

    protected function registerPages(): void
    {
        if (! config('filament-page-manager.register_pages', true)) {
            return;
        }

        if (! $this->app->routesAreCached()) {
            PageRoutes::register();
        }
    }

    protected function getAssetPackageName(): ?string
    {
        return 'cube-agency/filament-page-manager';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Css::make('filament-page-manager-styles', __DIR__ . '/../resources/dist/filament-page-manager.css'),
            Js::make('filament-page-manager-scripts', __DIR__ . '/../resources/dist/filament-page-manager.js'),
        ];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament_pages_table',
        ];
    }

    private function copyRoutesFile(): void
    {
        $path = base_path('routes/pages.php');
        $this->copyStub('pages', $path);
    }

    protected function copyStub(string $stub, string $targetPath): void
    {
        $filesystem = app(Filesystem::class);

        if (! $this->fileExists($stubPath = base_path("stubs/{$stub}.stub"))) {
            $stubPath = __DIR__ . "/../stubs/{$stub}.stub";
        }

        $stub = Str::of($filesystem->get($stubPath));

        $stub = (string) $stub;

        $this->writeFile($targetPath, $stub);
    }

    protected function purgeOutdatedRouteCache(): void
    {
        if (! config('filament-page-manager.clear_obsolete_route_cache')) {
            return;
        }

        if ($this->app->routesAreCached() && PageRoutesCache::isRouteCacheObsolete()) {
            PageRoutesCache::clearCache();
        }
    }

    protected function refreshObsoleteRouteCache(): void
    {
        if (! config('filament-page-manager.refresh_route_cache')) {
            return;
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('filament-page-manager:route-cache')->everyMinute();
        });
    }

    protected function getCommands(): array
    {
        return [
            RouteCacheCommand::class,
        ];
    }
}
