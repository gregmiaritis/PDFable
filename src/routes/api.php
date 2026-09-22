<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);
Route::middleware(['api.token', 'json'])->group(function () {
    Route::post('pdf', [PdfController::class, 'generateByHTML']);
    Route::post('pdf/url', [PdfController::class, 'generateByUrl']);
    Route::post('pdf/base64', [PdfController::class, 'generateByBase64']);
});
