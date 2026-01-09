<?php

use CubeAgency\FilamentPageManager\Models\Page;

return [
    'table_name' => 'filament_pages',

    'model' => Page::class,

    'route_name_prefix' => 'page',
    'route_middleware' => ['web'],

    'register_routes' => true,
    'register_pages' => true,

    'clear_obsolete_route_cache' => true,
    'refresh_route_cache' => true,

    'max_depth' => 5,

    'previews' => [
        'enabled' => false,
        'table_name' => 'filament_page_previews',
    ]
];
