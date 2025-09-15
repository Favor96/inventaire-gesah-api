<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CategorieProduitController;
use App\Http\Controllers\InventaireStockController;
use App\Http\Controllers\LigneInventaireStockController;
use App\Http\Controllers\InventaireAchatController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\LigneAchatController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\InventaireCaisseController;
use App\Http\Controllers\MouvementCaisseController;
use App\Http\Controllers\FournisseurController;

Route::apiResource('ventes', VenteController::class);
Route::apiResource('factures', FactureController::class);
Route::apiResource('produits', ProduitController::class);
Route::apiResource('categories-produits', CategorieProduitController::class);
Route::apiResource('inventaires-stocks', InventaireStockController::class);
Route::apiResource('lignes-inventaire-stocks', LigneInventaireStockController::class);
Route::apiResource('inventaires-achats', InventaireAchatController::class);
Route::apiResource('achats', AchatController::class);
Route::apiResource('lignes-achats', LigneAchatController::class);
Route::apiResource('caisses', CaisseController::class);
Route::apiResource('inventaires-caisses', InventaireCaisseController::class);
Route::apiResource('mouvements-caisses', MouvementCaisseController::class);
Route::apiResource('fournisseurs', FournisseurController::class);
