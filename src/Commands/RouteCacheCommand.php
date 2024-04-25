<?php

namespace CubeAgency\FilamentPageManager\Commands;

use CubeAgency\FilamentPageManager\Services\PageRoutesCache;
use Illuminate\Console\Command;

class RouteCacheCommand extends Command
{
    /**
     * @var string
     */
    protected $description = 'Laravel route:cache wrapper with Filament page manager Pages update detection';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-page-manager:route-cache {--json}';

    public function handle(): void
    {
        $jsonOutput = $this->option('json');
        $updated = false;

        if (PageRoutesCache::isRouteCacheObsolete()) {
            PageRoutesCache::cacheRoutes();
            $updated = true;
        }

        if ($jsonOutput) {
            $this->info(json_encode(['updated' => $updated]));
        } elseif ($updated) {
            $this->info("Obsolete route cache refreshed");
        }
    }
}
