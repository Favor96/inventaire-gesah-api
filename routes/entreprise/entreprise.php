<?php

use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\ClientEntrepriseController;
use App\Http\Controllers\EmployeEntrepriseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ImmobilisationController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\VenteController;
use App\Models\Caisse;

Route::prefix('entreprise')->middleware(['auth'])->group(function () {
    Route::apiResource('', EntrepriseController::class)->parameters([
        'entreprises' => 'hashid'
    ]);
    Route::post('/verify', [EntrepriseController::class, 'verify']);
    Route::apiResource('produits', ProduitController::class)->parameters([
        'produits' => 'hashid'
    ]);
    Route::apiResource('clients', ClientEntrepriseController::class)->parameters([
        'clients' => 'hashid'
    ]);
    Route::apiResource('employe', EmployeEntrepriseController::class)->parameters([
        'employe' => 'hashid'
    ]);
    Route::apiResource('fournisseur', FournisseurController::class)->parameters([
        'fournisseur' => 'hashid'
    ]);
    Route::apiResource('immobilisation', ImmobilisationController::class)->parameters([
        'immobilisation' => 'hashid'
    ]);
    Route::apiResource('vente', VenteController::class)->parameters([
        'vente' => 'hashid'
    ]);
    Route::apiResource('achat', AchatController::class)->parameters([
        'achat' => 'hashid'
    ]);
    Route::apiResource('abonement', AbonnementController::class)->parameters([
        'abonement' => 'hashid'
    ]);
    Route::apiResource('caisse', Caisse::class)->parameters([
        'caisse' => 'hashid'
    ]);
});