<?php

namespace CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages;

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource;
use CubeAgency\FilamentPageManager\Services\PageRoutesCache;
use CubeAgency\FilamentPageManager\Services\SlugGenerator;
use CubeAgency\FilamentTreeView\Resources\Pages\TreeViewRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ListPages extends TreeViewRecords
{
    protected static string $resource = PageResource::class;

    public function getActions(): array
    {
        return [
            Action::make('create')
                ->authorize(fn () => $this->canCreate())
                ->schema($this->actionSchema())
                ->action(function (array $data): void {
                    $parameters = http_build_query($data);

                    $this->redirect(static::$resource::getUrl('create') . '?' . $parameters);
                }),
        ];
    }

    public function getRowActions(Model $row): array
    {
        $actions = [
            ($this->replicateAction())(['row' => $row]),
            ($this->editAction())(['row' => $row]),
            ($this->deleteAction())(['row' => $row]),
        ];

        if ($row->depth < $this->getMaxDepth()) {
            array_unshift($actions, ($this->createChildAction())(['row' => $row]));
        }

        return $actions;
    }

    public function createChildAction(): Action
    {
        return Action::make('createChild')
            ->icon('heroicon-o-plus')
            ->authorize(fn () => $this->canCreate())
            ->fillForm(function (array $data, array $arguments) {
                $data['parentId'] = $arguments['row']['id'];

                return $data;
            })
            ->schema($this->actionSchema())
            ->action(function (array $data) {
                $parameters = http_build_query($data);

                $this->redirect(static::$resource::getUrl('create') . '?' . $parameters);
            });
    }

    public function replicateAction(): Action
    {
        return Action::make('replicate')
            ->icon('heroicon-o-document-duplicate')
            ->authorize(function (array $arguments) {
                $model = app(static::getModel());
                $row = $model->newInstance($arguments['row'])
                    ->forceFill(['id' => $arguments['row']['id']]);

                return $this->canReplicate($row);
            })
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $row = $this->getModel()::find($arguments['row']['id']);

                $newRow = $row->replicate();
                if ($row->parent) {
                    $newRow->appendToNode($row->parent);
                }

                $newRow->fill([
                    'slug' => SlugGenerator::generate($row, $row->slug),
                ])->save();

                $this->redirect(static::$resource::getUrl());
            })
            ->after(fn () => PageRoutesCache::setLastUpdateTimestamp(time()));
    }

    public function canReplicate(Model $row): bool
    {
        if (! $this->hasPermissions) {
            return true;
        }

        if (! $this->hasUserOnlyPolicy) {
            return static::getResource()::canReplicate($row);
        }

        return $this->permissionsCache['canReplicate'] = $this->permissionsCache['canReplicate']
            ?? static::getResource()::canReplicate($row);
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->icon('heroicon-o-trash')
            ->modalIcon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->authorize(function (array $arguments) {
                $model = app(static::getModel());
                $row = $model->newInstance($arguments['row'])
                    ->forceFill(['id' => $arguments['row']['id']]);

                return $this->canDelete($row);
            })
            ->action(function (array $arguments) {
                $row = $this->getModel()::find($arguments['row']['id']);

                $row?->delete();

                $this->redirect(static::$resource::getUrl());
            })
            ->after(fn () => PageRoutesCache::setLastUpdateTimestamp(time()));
    }

    protected function getTemplates(): Collection
    {
        return collect(config('filament-template.pages', []))->mapWithKeys(function ($template) {
            $templateName = explode('\\', $template);
            $templateName = Str::of(end($templateName))->replace('Template', '')->toString();

            return [$template => $templateName];
        });
    }

    protected function actionSchema(): array
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

    public function getMaxDepth(): int
    {
        return config('filament-page-manager.max_depth');
    }
}
