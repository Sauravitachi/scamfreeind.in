<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppLeadController;
use App\Http\Controllers\Api\ScamStatusReportController;
use App\Http\Controllers\Api\ScamLeadController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\AstrologerConsultationController;
use App\Http\Controllers\Api\LawyerController;
use App\Http\Controllers\Api\VltradingController;
use App\Http\Controllers\Admin\HomeController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/scam-status-report', [ScamStatusReportController::class, 'index'])->middleware(['web', 'auth']);
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('/latest', [BlogController::class, 'latest']);
    Route::get('/{slug}', [BlogController::class, 'show']);
});

Route::post('/whatsapp/lead', [WhatsAppLeadController::class, 'store']);
Route::post('/scam/lead', [ScamLeadController::class, 'store']);
Route::post('/lawyer/lead', [LawyerController::class, 'create']);
Route::post('/vltrading/lead', [VltradingController::class, 'store']);
Route::get('/lawyer/problem-types', [LawyerController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);
Route::get('/video-section', [HomeController::class, 'getVideoSectionData']);
Route::get('/expert-section', [HomeController::class, 'getExpertSectionData']);
Route::get('/astrologers', [HomeController::class, 'getExpertSectionData']);
Route::post('/astrologer/consult', [AstrologerConsultationController::class, 'store']);
Route::get('/lawyer/list', [LawyerController::class, 'lawyers']);