# Page manager for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cube-agency/filament-page-manager.svg?style=flat-square)](https://packagist.org/packages/cube-agency/filament-page-manager)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cube-agency/filament-page-manager/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cube-agency/filament-page-manager/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cube-agency/filament-page-manager/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cube-agency/filament-page-manager/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cube-agency/filament-page-manager.svg?style=flat-square)](https://packagist.org/packages/cube-agency/filament-page-manager)

Template based Page manager for FilamentPHP

## Installation

You can install the package via composer:

```bash
composer require cube-agency/filament-page-manager
```

Run install command:

```bash
php artisan filament-page-manager:install

```

Run migrations (if that was not done on install):

```bash
php artisan migrate
```

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-page-manager-config"
```

## Available Configuration Options

* **table_name**: The database table name used for pages.
* **route_name_prefix**: Prefix applied to generated route names.
* **route_middleware**: Middleware applied to page routes.
* **clear_obsolete_route_cache**: When set to true, clears obsolete routes from the cache (requires scheduler).
* **refresh_route_cache**: When set to true, the route cache will be refreshed after changes.
* **max_depth**: Controls the maximum nesting level of the tree view for pages.

## Usage

Add this plugin to your AdminPanelProvider
```php
use CubeAgency\FilamentPageManager\FilamentPageManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentPageManagerPlugin::make(),
        ]);
}
```

Create new template(s) according to this readme: [Filament Template](https://github.com/cube-agency/filament-template)

Add these templates in config/filament-template.php under "pages" key, for example:
```php
<?php

return [
    'pages' => [
        \App\Filament\Templates\MainTemplate::class
    ]
];
```

## Customization

You can create your own PageResource and override default one in AdminPanelProvider:
```php
use CubeAgency\FilamentPageManager\FilamentPageManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentPageManagerPlugin::make()
                ->resource(\App\Filament\Resources\PageResource::class),
        ]);
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Dmitrijs Mihailovs](https://github.com/dmitrijs.mihailovs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
