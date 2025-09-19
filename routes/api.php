<?php

use App\Http\Controllers\CategorieProduitController;
use App\Http\Controllers\EntrepriseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('entreprises', [EntrepriseController::class, 'index'])->middleware(['auth:sanctum', 'role:agent,admin,chef']);


require __DIR__.'/auth/auth.php';
require __DIR__.'/administrateur/admin.php';
require __DIR__.'/entreprise/entreprise.php';
require __DIR__.'/chef/chef.php';
require __DIR__.'/agent/agent.php';