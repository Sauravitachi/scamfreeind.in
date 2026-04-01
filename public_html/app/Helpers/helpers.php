<?php

use App\Services\AppUiService;

if (!function_exists('app_ui')) {
    /**
     * Helper to get App UI data easily.
     */
    function app_ui(string $name, ?string $key = null, $default = null)
    {
        return app(AppUiService::class)->get($name, $key, $default);
    }
}
