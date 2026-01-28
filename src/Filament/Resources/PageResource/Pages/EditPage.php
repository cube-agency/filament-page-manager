<?php

namespace CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages;

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource;
use CubeAgency\FilamentPageManager\Models\PagePreview;
use CubeAgency\FilamentPageManager\Services\PageRoutesCache;
use CubeAgency\FilamentPageManager\Traits\PageFormTrait;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditPage extends EditRecord
{
    use PageFormTrait;

    protected static string $resource = PageResource::class;

    protected function afterSave(): void
    {
        PageRoutesCache::setLastUpdateTimestamp(time());
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getPreviewAction(),
            ...parent::getHeaderActions(),
        ];
    }

    public function getPreviewAction(): Action
    {
        return Action::make('preview')
            ->label('Preview')
            ->button()
            ->icon('heroicon-o-eye')
            ->action(function () {
                $record = $this->record;
                $data = $this->form->getState();

                $preview = PagePreview::query()
                    ->create([
                        'page_id' => $record->getKey(),
                        'data' => $data,
                        'token' => Str::random(32),
                        'expires_at' => now()->addDay(),
                    ]);

                $url = route('filament-page-manager.pages.preview', $preview->token);

                $this->dispatch('filament-page-manager::preview', url: $url);
            })
            ->visible(config('filament-page-manager.previews.enabled', false));
    }
}
