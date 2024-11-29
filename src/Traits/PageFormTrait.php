<?php

namespace CubeAgency\FilamentPageManager\Traits;

use CubeAgency\FilamentJson\Filament\Forms\Components\Json;
use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentTemplate\FilamentTemplate;
use CubeAgency\FilamentTemplate\Forms\Components\Template;
use Filament\Forms\Components\Actions\Action;
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
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
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
                                    ->afterStateUpdated(function (Set $set, ?string $state, string $context) {
                                        if ($context == 'create') {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->prefix(function ($record) {
                                        $currentRecord = $this->getCurrentRecord($record);

                                        return $this->getRecordUrl(record: $currentRecord, withThis: ! $record);
                                    })
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', Str::slug($state));
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
                                            ->alpineClickHandler(function ($record, $state) {
                                                $currentRecord = $this->getCurrentRecord($record);
                                                $url = $this->getRecordUrl(record: $currentRecord, state: $state, withThis: ! $record);

                                                return '
                                                    window.navigator.clipboard.writeText("' . $url . '");
                                                    $tooltip(\'Copied\', {theme: $store.theme, timeout: 2000})
                                                ';
                                            }),
                                    ])
                                    ->helperText(function ($record, $state) {
                                        if (! $record) {
                                            return '';
                                        }

                                        $currentRecord = $this->getCurrentRecord($record);
                                        $url = $this->getRecordUrl(record: $currentRecord, state: $state);

                                        return new HtmlString('<a href="' . $url . '" class="text-primary-600" target="_blank">' . $url . '</a>');
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

    protected function getCurrentRecord(?Model $record = null): ?Model
    {
        return $record ?? ($this->parentId ? Page::find($this->parentId) : null);
    }

    protected function getRecordUrl(?Model $record = null, $state = null, ?bool $withThis = false)
    {
        $state = $state === '/' ? '' : $state;

        if ($record) {
            return e($record->getFullUrl($withThis) . $state);
        }

        return e(config('app.url') . '/' . $state);
    }
}
