<?php

namespace CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages;

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource;
use CubeAgency\FilamentPageManager\Traits\PageFormTrait;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use PageFormTrait;

    protected static string $resource = PageResource::class;
}
