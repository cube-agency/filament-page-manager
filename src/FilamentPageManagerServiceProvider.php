<?php

namespace CubeAgency\FilamentPageManager;

use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Filesystem\Filesystem;
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
    }

    public function packageRegistered(): void
    {
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
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

        if (!$this->fileExists($stubPath = base_path("stubs/{$stub}.stub"))) {
            $stubPath = __DIR__ . "/../stubs/{$stub}.stub";
        }

        $stub = Str::of($filesystem->get($stubPath));

        $stub = (string)$stub;

        $this->writeFile($targetPath, $stub);
    }
}
