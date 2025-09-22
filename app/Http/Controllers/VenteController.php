<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\LigneVente;
use App\Models\Produit;
use App\Models\ProduitPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class VenteController extends Controller
{
    public function index()
    {
        try {
            $ventes = Vente::with(['client', 'lignesVente'])->get();
            return response()->json(['ventes' => $ventes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:client_entreprises,id',
                'date_vente' => 'required|date',
                'statut' => 'required|in:en_attente,payee,annule',
                'lignes' => 'required|array|min:1',
                'lignes.*.package_id' => 'required|exists:produit_packages,id',
                'lignes.*.quantite' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $total = 0;
            $alertes = [];

            // Vérification stock avant création
            foreach ($request->lignes as $ligne) {
                $package = ProduitPackage::find($ligne['package_id']);
                if (!$package) continue;

                if ($ligne['quantite'] > $package->stock_actuel) {
                    $alertes[] = "Le package {$package->id} ({$package->produit->nom}) ne dispose pas de quantité suffisante pour cette vente.";
                }
            }

            if (!empty($alertes)) {
                return response()->json([
                    'message' => 'Quantité supérieure au stock disponible',
                    'alertes' => $alertes
                ], 422);
            }

            // Création de la vente
            $vente = Vente::create([
                'client_id' => $request->client_id,
                'date_vente' => $request->date_vente,
                'total' => 0,
                'statut' => $request->statut,
            ]);

            foreach ($request->lignes as $ligne) {
                $package = ProduitPackage::find($ligne['package_id']);
                if (!$package) continue;

                $montant_ligne = $ligne['quantite'] * $package->prix;

                LigneVente::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $package->produit_id,
                    'package_id' => $package->id,
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $package->prix,
                    'montant_ligne' => $montant_ligne,
                ]);

                $total += $montant_ligne;

                // Diminuer le stock si statut payee
                if ($request->statut === 'payee') {
                    $package->stock_actuel -= $ligne['quantite'];
                    $package->save();
                }
            }

            $vente->update(['total' => $total]);

            $vente = Vente::with(['client', 'lignesVente'])->find($vente->id);

            return response()->json(['message' => 'Vente créée avec succès', 'vente' => $vente], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }



    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $vente = Vente::with(['client', 'lignesVente'])->find($ids[0]);
            if (!$vente) return response()->json(['message' => 'Vente non trouvée'], 404);

            return response()->json(['vente' => $vente], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $vente = Vente::with('lignesVente')->find($ids[0]);
            if (!$vente) return response()->json(['message' => 'Vente non trouvée'], 404);

            $validator = Validator::make($request->all(), [
                'client_id' => 'sometimes|exists:client_entreprises,id',
                'date_vente' => 'sometimes|date',
                'statut' => 'sometimes|in:en_attente,payee,annule',
                'lignes' => 'nullable|array|min:1',
                'lignes.*.package_id' => 'required_with:lignes|exists:produit_packages,id',
                'lignes.*.quantite' => 'required_with:lignes|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $ancien_statut = $vente->statut;
            $nouveau_statut = $request->statut ?? $ancien_statut;


            $alertes = [];

            // Vérification du stock si lignes fournies
            if ($request->has('lignes')) {
                foreach ($request->lignes as $ligne) {
                    $package = ProduitPackage::find($ligne['package_id']);
                    if (!$package) continue;

                    $quantite_disponible = $package->stock_actuel;
                    $ancienne_quantite = $vente->lignesVente->where('package_id', $package->id)->sum('quantite');
                    if ($ligne['quantite'] > $quantite_disponible + $ancienne_quantite) {
                        $alertes[] = "Le package {$package->id} ({$package->produit->nom}) ne dispose pas de quantité suffisante pour cette vente.";
                    }
                }

                if (!empty($alertes)) {
                    return response()->json([
                        'message' => 'Quantité supérieure au stock disponible',
                        'alertes' => $alertes
                    ], 422);
                }
            }

            // Gestion du statut 'annule' pour toutes les lignes
            if ($nouveau_statut === 'annule') {
                foreach ($vente->lignesVente as $ligne) {
                    // Remettre le stock si la vente était payee
                    if ($ancien_statut === 'payee') {
                        $package = ProduitPackage::find($ligne->package_id);
                       
                        if ($package) {
                            $package->stock_actuel += $ligne->quantite;
                            $package->save();
                        }
                    }
                    $ligne->statut = 'annule';
                    $ligne->save();
                }
            }

            // Mise à jour des lignes si fournies
            if ($request->has('lignes')) {
                $total = 0;

                foreach ($request->lignes as $ligne) {
                    $package = ProduitPackage::find($ligne['package_id']);
                    if (!$package) continue;

                    $montant_ligne = $ligne['quantite'] * $package->prix;

                    // Vérifier si une ligne existante
                    $ligne_existante = $vente->lignesVente->where('package_id', $package->id)->first();
                    if ($ligne_existante) {
                        $ligne_existante->quantite = $ligne['quantite'];
                        $ligne_existante->prix_unitaire = $package->prix;
                        $ligne_existante->montant_ligne = $montant_ligne;
                        $ligne_existante->statut = $nouveau_statut === 'annule' ? 'annule' : 'active';
                        $ligne_existante->save();
                    } else {
                        LigneVente::create([
                            'vente_id' => $vente->id,
                            'produit_id' => $package->produit_id,
                            'package_id' => $package->id,
                            'quantite' => $ligne['quantite'],
                            'prix_unitaire' => $package->prix,
                            'montant_ligne' => $montant_ligne,
                            'statut' => $nouveau_statut === 'annule' ? 'annule' : 'active',
                        ]);
                    }

                    $total += $montant_ligne;

                    // Diminuer le stock si statut payee
                    if ($nouveau_statut === 'payee') {
                        $package->stock_actuel -= $ligne['quantite'];
                        if ($package->stock_actuel < 0) $package->stock_actuel = 0;
                        $package->save();
                    }
                }

                $vente->total = $total;
            }

            // Mise à jour du reste de la vente
            $vente->update($request->only(['client_id', 'date_vente', 'statut', 'total']));

            $vente = Vente::with(['client', 'lignesVente'])->find($vente->id);

            return response()->json(['message' => 'Vente mise à jour', 'vente' => $vente], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }


    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $vente = Vente::with('lignesVente')->find($ids[0]);
            if (!$vente) return response()->json(['message' => 'Vente non trouvée'], 404);

            // Restaurer stock si la vente était payee
            if ($vente->statut === 'payee') {
                foreach ($vente->lignesVente as $ligne) {
                    $produit = Produit::find($ligne->produit_id);
                    if ($produit) {
                        $produit->stock_actuel += $ligne->quantite;
                        $produit->save();
                    }
                }
            }

            $vente->delete();

            return response()->json(['message' => 'Vente supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
