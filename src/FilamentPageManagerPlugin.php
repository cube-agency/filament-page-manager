<?php

namespace CubeAgency\FilamentPageManager;

use Closure;
use CubeAgency\FilamentPageManager\Filament\Resources\PageResource;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Panel;

class FilamentPageManagerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-page-manager';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                PageResource::class,
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
}
