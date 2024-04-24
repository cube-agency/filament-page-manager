<?php

namespace CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages;

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource;
use CubeAgency\FilamentPageManager\Services\SlugGenerator;
use CubeAgency\FilamentTreeView\Resources\Pages\TreeViewRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ListPages extends TreeViewRecords
{
    protected static string $resource = PageResource::class;

    public function getActions(): array
    {
        return [
            Action::make('create')
                ->form($this->actionForm())
                ->action(function (array $data): void {
                    $parameters = http_build_query($data);

                    $this->redirect(static::$resource::getUrl('create') . '?' . $parameters);
                }),
        ];
    }

    public function getRowActions(Model $row): array
    {
        return [
            ($this->createChildAction())(['row' => $row->getKey()]),
            ($this->cloneAction())(['row' => $row->getKey()]),
            ($this->editAction())(['row' => $row->getKey()]),
            ($this->deleteAction())(['row' => $row->getKey()]),
        ];
    }

    public function createChildAction(): Action
    {
        return Action::make('createChild')
            ->fillForm(function (array $data, array $arguments) {
                $data['parentId'] = $arguments['row'];

                return $data;
            })
            ->form($this->actionForm())
            ->action(function (array $data) {
                $parameters = http_build_query($data);

                $this->redirect(static::$resource::getUrl('create') . '?' . $parameters);
            });
    }

    public function cloneAction(): Action
    {
        return Action::make('clone')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $row = $this->getModel()::find($arguments['row']);

                $newRow = $row->replicate();
                if ($row->parent) {
                    $newRow->appendToNode($row->parent);
                }

                $newRow->fill([
                    'slug' => SlugGenerator::generate($row, $row->slug),
                ])->save();

                Artisan::call('route:cache');

                $this->redirect(static::$resource::getUrl());
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->color('danger')
            ->modalIcon('heroicon-o-trash')
            ->action(function (array $arguments) {
                $row = $this->getModel()::find($arguments['row']);

                $row?->delete();

                Artisan::call('route:cache');

                $this->redirect(static::$resource::getUrl());
            });
    }

    protected function getTemplates(): Collection
    {
        return collect(config('filament-template.pages', []))->mapWithKeys(function ($template) {
            $templateName = explode('\\', $template);
            $templateName = Str::of(end($templateName))->replace('Template', '');

            return [$template => end($templateName)];
        });
    }

    protected function actionForm(): array
    {
        return [
            Hidden::make('parentId'),
            Select::make('template')
                ->label('Template')
                ->options($this->getTemplates())
                ->required(),
        ];
    }

    public function getRowClasses(Model $row): array
    {
        return [
            'inactive' => ! $row->active,
        ];
    }

    public function getRowSuffix(Model $row): ?string
    {
        $template = explode('\\', $row->template);

        return Str::of(end($template))->replace('Template', '');
    }
}
