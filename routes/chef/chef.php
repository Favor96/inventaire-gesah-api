<?php

use App\Http\Controllers\AgentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChefMissionController;

Route::prefix('chef')->middleware(['auth:sanctum','chef.auth'])->group(function () {
    Route::apiResource('agents', AgentController::class)->parameters([
        'agents' => 'hashid'
    ]);
});