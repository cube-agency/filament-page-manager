<?php

namespace CubeAgency\FilamentPageManager\Traits;

use CubeAgency\FilamentJson\Filament\Forms\Components\Json;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentTemplate\FilamentTemplate;
use CubeAgency\FilamentTemplate\Forms\Components\Template;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait PageFormTrait
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->prefix(function ($record) {
                                        return config('app.url') . '/' . ($record ? $record->getUri(false) : '');
                                    })
                                    ->required()
                                    ->unique(Page::class, 'slug', fn($record) => $record),

                                Json::make('meta')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('title'),
                                                TagsInput::make('keywords'),
                                            ]),
                                        Grid::make()
                                            ->schema([
                                                Textarea::make('description')
                                                    ->rows(3),
                                                FileUpload::make('image')
                                                    ->image()
                                                    ->imagePreviewHeight('64')
                                            ]),
                                    ]),

                                Hidden::make('template')
                                    ->default($this->getTemplate()),

                                Template::make('content')
                                    ->template($this->resolveTemplate()),
                            ])
                    ])
                    ->columnSpan(['lg' => 9]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                DateTimePicker::make('activate_at')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('deactivate', null);
                                    })
                                    ->default(now()),

                                DateTimePicker::make('expire_at'),

                                Toggle::make('deactivate')
                                    ->label('Deactivate')
                                    ->live()
                                    ->hidden(fn(?Model $record): bool => $record === null || !$record->active)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('activate_at', null);
                                        $set('expire_at', null);
                                    })
                            ]),
                    ])
                    ->columnSpan(['lg' => 3]),
            ])->columns(12);
    }

    protected function resolveTemplate(): FilamentTemplate
    {
        return resolve($this->getTemplate());
    }

    protected function getTemplate(): ?string
    {
        if (property_exists($this, 'template')) {
            return $this->template;
        }

        return $this->getRecord()->getAttribute('template');
    }
}
