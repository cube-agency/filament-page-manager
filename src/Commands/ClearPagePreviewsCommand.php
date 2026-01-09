<?php

namespace CubeAgency\FilamentPageManager\Commands;

use CubeAgency\FilamentPageManager\Models\PagePreview;
use Illuminate\Console\Command;

class ClearPagePreviewsCommand extends Command
{
    /**
     * @var string
     */
    protected $description = 'Clear old page previews';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-page-manager:clear-page-previews';

    public function handle(): void
    {
        PagePreview::query()->where('expires_at', '<=', now())->delete();

        $this->info('Page previews cleared.');
    }
}
