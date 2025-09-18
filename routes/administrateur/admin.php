<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdministrateurController;
use App\Http\Controllers\ChefMissionController;
use App\Http\Controllers\PlanAbonnementController;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum','admin.auth'])->group(function () {
        Route::apiResource('administrateurs', AdministrateurController::class)->parameters([
            'administrateurs' => 'hashid'
        ]);
        Route::apiResource('chef', ChefMissionController::class)->parameters([
            'chef' => 'hashid'
        ]);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('plan', PlanAbonnementController::class)->parameters([
            'plan' => 'hashid'
        ]);
    });
});