<?php

namespace CubeAgency\FilamentPageManager\Tests;

use CubeAgency\FilamentPageManager\FilamentPageManagerPlugin;
use Filament\Panel;
use Filament\PanelProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->plugin(FilamentPageManagerPlugin::make());
    }
}
