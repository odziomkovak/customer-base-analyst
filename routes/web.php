<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class);

Route::post('/upload', UploadController::class);
Route::get('/analysis/stream', AnalysisController::class);
