<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdministrateurController;
use App\Http\Controllers\ChefMissionController;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['admin.auth'])->group(function () {
        Route::apiResource('administrateurs', AdministrateurController::class)->parameters([
            'administrateurs' => 'hashid'
        ]);
        Route::apiResource('chef', ChefMissionController::class)->parameters([
            'chef' => 'hashid'
        ]);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});