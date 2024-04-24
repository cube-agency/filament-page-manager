<?php

namespace CubeAgency\FilamentPageManager\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use CubeAgency\FilamentPageManager\Models\Page;

class PageRoutes
{
    public static function for(string $page, Closure $routes): void
    {
        if (!self::isDatabaseConfigured()) {
            return;
        }

        Page::query()
            ->where('template', $page)
            ->get()
            ->map(function ($templatePage) use ($routes) {
                Route::name(config('filament-page-manager.route_name_prefix') . '.' . $templatePage->getKey() . '.')
                    ->group(function () use ($routes, $templatePage) {
                        $routes($templatePage);
                    });
            });
    }

    protected static function isDatabaseConfigured(): bool
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception) {
            return false;
        }

        return Schema::hasTable(config('filament-page-manager.table_name'));
    }
}
