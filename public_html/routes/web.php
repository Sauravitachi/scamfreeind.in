<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\SocialMediaWebhookController;

Route::get('/', function () {
    return view('welcome');
});

// Public route to serve public files from storage
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path('app/public/' . $folder . '/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $mimeType = mime_content_type($path);
    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('folder', '.*');

Route::post('webhook/social-media', [SocialMediaWebhookController::class, 'handle']);

