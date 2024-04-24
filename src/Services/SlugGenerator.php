<?php

namespace CubeAgency\FilamentPageManager\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SlugGenerator
{
    public static function generate(Model $model, string $value, $id = 0): string
    {
        $slug = Str::slug($value);
        $allSlugs = self::getRelatedSlugs($model, $slug, $id);

        if (!$allSlugs->contains('slug', $slug)) {
            return $slug;
        }

        for ($i = 1; $i <= 100; $i++) {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                return $newSlug;
            }
        }

        throw new \Exception('Can not create a unique slug');
    }

    protected static function getRelatedSlugs(Model $model, $slug, $id = 0): Collection
    {
        return $model::query()
            ->select(['id', 'slug'])
            ->where('slug', 'like', $slug . '%')
            ->where('id', '<>', $id)
            ->get();
    }
}
