<?php

namespace CubeAgency\FilamentPageManager\Traits;

use CubeAgency\FilamentJson\Filament\Forms\Components\Json;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentTemplate\Exceptions\TemplateNotFoundException;
use CubeAgency\FilamentTemplate\FilamentTemplate;
use CubeAgency\FilamentTemplate\Forms\Components\Template;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait PageFormTrait
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Set $set, ?string $state, string $context) {
                                        if ($context === 'create') {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->prefix(function ($record) {
                                        $currentRecord = $this->getCurrentRecord($record);

                                        return $this->getRecordUrl(record: $currentRecord, withThis: ! $record);
                                    })
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state !== '/' && $state !== Str::slug($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->unique(Page::class, 'slug', fn ($record) => $record)
                                    ->suffixActions([
                                        Action::make('refresh')
                                            ->icon('heroicon-m-arrow-path')
                                            ->action(function (Set $set, Get $get) {
                                                $set('slug', Str::slug($get('name')));
                                            })
                                            ->visible(fn ($context) => $context === 'edit'),

                                        Action::make('copy')
                                            ->icon('heroicon-m-clipboard')
                                            ->extraAttributes(['x-data' => 'pageManager'])
                                            ->action(function ($record, $state) {
                                                $currentRecord = $this->getCurrentRecord($record);
                                                $url = $this->getRecordUrl(record: $currentRecord, state: $state, withThis: ! $record);

                                                $this->dispatch('filament-page-manager::copy-url', url: $url);
                                            }),
                                    ])
                                    ->helperText(function ($record, $state) {
                                        if (! $record) {
                                            return '';
                                        }

                                        $currentRecord = $this->getCurrentRecord($record);
                                        $url = $this->getRecordUrl(record: $currentRecord, state: $state);

                                        return view('filament-page-manager::partials.url', ['url' => $url]);
                                    }),

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
                                                    ->imagePreviewHeight('64'),
                                            ]),
                                    ]),

                                Hidden::make('template')
                                    ->default($this->getTemplate()),

                                Template::make('content')
                                    ->template($this->resolveTemplate()),
                            ]),
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
                                    ->hidden(fn (?Model $record): bool => $record === null || ! $record->active)
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('activate_at', null);
                                        $set('expire_at', null);
                                    }),
                            ]),
                    ])
                    ->columnSpan(['lg' => 3]),
            ])->columns(12);
    }

    /**
     * @throws TemplateNotFoundException
     */
    protected function resolveTemplate(): FilamentTemplate
    {
        if (! $this->getTemplate()) {
            throw new TemplateNotFoundException('Template not found');
        }

        return resolve($this->getTemplate());
    }

    protected function getTemplate(): ?string
    {
        if (property_exists($this, 'template')) {
            return $this->template;
        }

        return $this->getRecord()?->getAttribute('template');
    }

    protected function getCurrentRecord(?Model $record = null): ?Model
    {
        return $record ?? ($this->parentId ? Page::find($this->parentId) : null);
    }

    protected function getRecordUrl(?Model $record = null, $state = null, ?bool $withThis = false): string
    {
        $state = $state === '/' ? '' : $state;

        if ($record) {
            return e($record->getFullUrl($withThis) . $state);
        }

        return e(config('app.url') . '/' . $state);
    }
}
