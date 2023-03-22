<?php

use App\Http\Controllers\AppStatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/appTopCategory', [AppStatisticsController::class, 'getTopPositions']);
