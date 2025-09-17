<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EntrepriseController;

Route::prefix('entreprise')->middleware(['auth'])->group(function () {
    Route::apiResource('entreprises', EntrepriseController::class)->parameters([
        'entreprises' => 'hashid'
    ]);
    Route::post('entreprises/verify', [EntrepriseController::class, 'verify']);
});