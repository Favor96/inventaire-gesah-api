<?php

use App\Http\Controllers\InventaireCaisseController;
use App\Http\Controllers\InventaireImmobilisationController;
use App\Http\Controllers\InventaireStockController;
use Illuminate\Support\Facades\Route;

Route::prefix('agents')->middleware(['auth:sanctum','agent.auth'])->group(function () {
    Route::apiResource('inventaire-stock', InventaireStockController::class)->parameters([
        'inventaire-stock' => 'hashid'
    ]);
    Route::apiResource('inventaire-caisse', InventaireCaisseController::class)->parameters([
        'inventaire-caisse' => 'hashid'
    ]);
    Route::apiResource('inventaire-immo', InventaireImmobilisationController::class)->parameters([
        'inventaire-immo' => 'hashid'
    ]);
});