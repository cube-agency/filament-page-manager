<?php

namespace CubeAgency\FilamentPageManager\Models;

use CubeAgency\FilamentPageManager\Database\Factories\PageFactory;
use CubeAgency\FilamentPageManager\Traits\HasActivationDates;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Page extends Model
{
    use HasFactory;
    use NodeTrait;
    use HasActivationDates;

    public function __construct(array $attributes = [])
    {
        $this->table = config('filament-page-manager.table_name');

        parent::__construct($attributes);
    }

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'template',
        'content',
        'meta',
        'activate_at',
        'expire_at'
    ];

    protected $casts = [
        'content' => 'array',
        'meta' => 'array'
    ];

    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }

    public function parents(): Collection
    {
        return $this->ancestors()->get();
    }

    public function getUri(bool $withThis = true): string
    {
        $uri = [];

        foreach ($this->parents() as $parent) {
            $uri[] = $parent->slug;
        }

        if ($withThis) {
            $uri[] = $this->slug;
        }

        return implode('/', $uri);
    }

    public function getRouteName(string $actionName = 'index'): string
    {
        return implode('.', [
            config('filament-page-manager.route_name_prefix'),
            $this->getKey(),
            $actionName
        ]);
    }

    public function getUrl(string $actionName = 'index', array $parameters = [], bool $absolute = true): string
    {
        try {
            return route($this->getRouteName($actionName), $parameters, $absolute);
        } catch (\Exception) {
            return '';
        }
    }

    protected static function newFactory(): Factory
    {
        return PageFactory::new();
    }
}
