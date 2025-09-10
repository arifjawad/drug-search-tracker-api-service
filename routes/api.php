<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MedicationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });

    // Public Drug Search route with rate limiting
    Route::get('drugs/search', [MedicationController::class, 'search'])->middleware('throttle:search');

    // Authenticated User Medication routes
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::get('/user/drugs', [MedicationController::class, 'index']);
        Route::post('/user/drugs', [MedicationController::class, 'add']);
        Route::delete('/user/drugs/{rxcui}', [MedicationController::class, 'delete']);
    });

});
