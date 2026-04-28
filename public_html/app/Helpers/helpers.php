<?php

use App\Services\AppUiDataService;

if (!function_exists('app_ui')) {
    /**
     * Helper to get App UI data easily.
     */
    function app_ui(string $name, ?string $key = null, $default = null)
    {
        return app(AppUiDataService::class)->get($name, $key, $default);
    }
}

if (!function_exists('imageNotFoundUrl')) {
    /**
     * Helper to get the default image URL when one is missing.
     */
    function imageNotFoundUrl()
    {
        return asset('not-found-image.png');
    }
}
