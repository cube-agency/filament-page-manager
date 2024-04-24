<?php

namespace CubeAgency\FilamentPageManager\Tests\Fixtures\Templates;

use CubeAgency\FilamentTemplate\FilamentTemplate;
use Filament\Forms\Components\Textarea;

class TestTemplate extends FilamentTemplate
{
    public function schema(): array
    {
        return [
            Textarea::make('textarea')
        ];
    }
}
