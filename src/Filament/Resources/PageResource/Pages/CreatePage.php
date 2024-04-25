<?php

namespace CubeAgency\FilamentPageManager\Filament\Resources\PageResource\Pages;

use CubeAgency\FilamentPageManager\Filament\Resources\PageResource;
use CubeAgency\FilamentPageManager\Services\PageRoutesCache;
use CubeAgency\FilamentPageManager\Traits\PageFormTrait;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Url;

class CreatePage extends CreateRecord
{
    use PageFormTrait;

    #[Url]
    public $template;

    #[Url]
    public $parentId;

    protected static string $resource = PageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /**
         * @var Model $model
         */
        $model = $this->getModel();
        $parent = $model::query()->find($this->parentId);

        return $model::create($data, $parent);
    }

    protected function afterCreate(): void
    {
        PageRoutesCache::setLastUpdateTimestamp(time());
    }
}
