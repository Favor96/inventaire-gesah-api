<?php

use App\Http\Controllers\CategorieProduitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('categorie-prod', CategorieProduitController::class)->parameters([
    'categorie-prod' => 'hashid'
]);
require __DIR__.'/auth/auth.php';
require __DIR__.'/administrateur/admin.php';
require __DIR__.'/entreprise/entreprise.php';
require __DIR__.'/chef/chef.php';
require __DIR__.'/agent/agent.php';