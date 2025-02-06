<?php

namespace CubeAgency\FilamentPageManager;

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentPageManagerPlugin implements Plugin
{
    protected string $resource = PageResource::class;

    public function getId(): string
    {
        return 'filament-page-manager';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                $this->getResource(),
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function resource(string $resource): static
    {
        $this->resource = $resource;

        return $this;
    }
}
