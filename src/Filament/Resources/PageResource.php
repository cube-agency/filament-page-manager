<?php

namespace CubeAgency\FilamentPageManager\Filament\Resources;

use BackedEnum;
use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\CreatePage;
use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\EditPage;
use CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages\ListPages;
use CubeAgency\FilamentPageManager\Models\Page;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class PageResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::ClipboardDocument;

    public static function getModel(): string
    {
        return config('filament-page-manager.model', Page::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
