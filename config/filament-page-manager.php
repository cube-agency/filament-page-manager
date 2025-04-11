<?php

use CubeAgency\FilamentPageManager\Models\Page;

return [
    'table_name' => 'filament_pages',

    'model' => Page::class,

    'route_name_prefix' => 'page',
    'route_middleware' => ['web'],

    'clear_obsolete_route_cache' => true,
    'refresh_route_cache' => true,

    'max_depth' => 5,
];
