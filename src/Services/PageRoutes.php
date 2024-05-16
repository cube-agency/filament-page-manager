<?php

namespace CubeAgency\FilamentPageManager\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use CubeAgency\FilamentPageManager\Models\Page;

class PageRoutes
{
    protected static array $registry = [];

    public static function for(string $page, Closure $routes): void
    {
        if (!self::isDatabaseConfigured()) {
            return;
        }

        self::$registry[$page] = $routes;
    }

    public static function register(): void
    {
        $orderByColumnName = config('filament-page-manager.order_by_column_name', '_lft');
        Page::query()
            ->orderByDesc($orderByColumnName)
            ->get(['id', $orderByColumnName, 'parent_id', 'name', 'slug', 'template', 'activate_at'])
            ->each(function ($page) {
                if (isset(self::$registry[$page->template])) {
                    Route::name(config('filament-page-manager.route_name_prefix') . '.' . $page->getKey() . '.')
                        ->group(function () use ($page) {
                            self::$registry[$page->template]($page);
                        });
                }
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
