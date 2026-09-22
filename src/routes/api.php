<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;

Route::get('/health', fn () => ['status' => 'ok']);
Route::post('pdf', [PdfController::class, 'show'])->middleware('api.token');
