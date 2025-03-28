<?php

return [
    'table_name' => 'filament_pages',

    'route_name_prefix' => 'page',
    'route_middleware' => ['web'],

    'clear_obsolete_route_cache' => true,
    'refresh_route_cache' => true,

    'max_depth' => 5,
];
