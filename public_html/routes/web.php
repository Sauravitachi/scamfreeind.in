<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\SocialMediaWebhookController;

Route::get('/', function () {
    return view('welcome');
});

// Public route to serve status-files from storage
Route::get('/storage/uploads/status-files/{filename}', function ($filename) {
    $path = storage_path('app/public/uploads/status-files/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    $mimeType = mime_content_type($path);
    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=86400',
    ]);
});

Route::post('webhook/social-media', [SocialMediaWebhookController::class, 'handle']);

