<?php

namespace CubeAgency\FilamentPageManager\Filament\Resources;

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\CreatePage;
use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\EditPage;
use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\ListPages;
use CubeAgency\FilamentPageManager\Models\Page;
use Filament\Resources\Resource;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
