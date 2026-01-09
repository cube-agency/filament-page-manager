<?php

use CubeAgency\FilamentPageManager\Models\Page;
use CubeAgency\FilamentPageManager\Models\PagePreview;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

if (config('filament-page-manager.previews.enabled', false)) {
    Route::get('pages/{token}/preview', function (Request $request, $token) {
        $preview = PagePreview::query()->where('token', $token)->firstOrFail();

        $model = config('filament-page-manager.model', Page::class);
        $page = new $model($preview->data);
        $template = app($page->template);

        $templateController = app($template->getController());

        $request->route()->setAction([
            'page' => $page,
        ]);

        if (! method_exists($templateController, 'index')) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $templateController->index($request);
    })->name('filament-page-manager.pages.preview')
        ->middleware(config('filament-page-manager.route_middleware', ['web']));
}
