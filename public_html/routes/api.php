<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppLeadController;
use App\Http\Controllers\Api\ScamLeadController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Admin\HomeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('/latest', [BlogController::class, 'latest']);
    Route::get('/{slug}', [BlogController::class, 'show']);
});

Route::post('/whatsapp/lead', [WhatsAppLeadController::class, 'store']);
Route::post('/scam/lead', [ScamLeadController::class, 'store']);
Route::post('/contact', [ContactController::class, 'store']);
Route::get('/video-section', [HomeController::class, 'getVideoSectionData']);