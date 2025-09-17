<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\LigneVente;
use App\Models\Produit;
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
                'lignes.*.produit_id' => 'required|exists:produits,id',
                'lignes.*.quantite' => 'required|integer|min:1',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            // Calcul du total
            $total = 0;
            foreach ($request->lignes as $ligne) {
                $total += $ligne['quantite'] * $ligne['prix_unitaire'];
            }

            // Création de la vente
            $vente = Vente::create([
                'client_id' => $request->client_id,
                'date_vente' => $request->date_vente,
                'total' => $total,
                'statut' => $request->statut,
            ]);

            // Création des lignes de vente
            foreach ($request->lignes as $ligne) {
                LigneVente::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $ligne['produit_id'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                ]);
            }

            // Si statut = payee, diminuer le stock
            if ($request->statut === 'payee') {
                foreach ($request->lignes as $ligne) {
                    $produit = Produit::find($ligne['produit_id']);
                    if ($produit) {
                        $produit->stock_actuel -= $ligne['quantite'];
                        if ($produit->stock_actuel < 0) $produit->stock_actuel = 0;
                        $produit->save();
                    }
                }
            }

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
                'lignes.*.produit_id' => 'required_with:lignes|exists:produits,id',
                'lignes.*.quantite' => 'required_with:lignes|integer|min:1',
                'lignes.*.prix_unitaire' => 'required_with:lignes|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            // Gestion du changement de statut pour le stock
            $ancien_statut = $vente->statut;
            $nouveau_statut = $request->statut ?? $ancien_statut;

            // Restaurer stock si l'ancien statut était payee et nouveau n'est pas payee
            if ($ancien_statut === 'payee' && $nouveau_statut !== 'payee') {
                foreach ($vente->lignesVente as $ligne) {
                    $produit = Produit::find($ligne->produit_id);
                    if ($produit) {
                        $produit->stock_actuel += $ligne->quantite;
                        $produit->save();
                    }
                }
            }

            // Si le nouveau statut est payee et ancien n'était pas payee, diminuer le stock
            if ($ancien_statut !== 'payee' && $nouveau_statut === 'payee') {
                foreach ($request->lignes ?? $vente->lignesVente->toArray() as $ligne) {
                    $produit = Produit::find($ligne['produit_id']);
                    if ($produit) {
                        $produit->stock_actuel -= $ligne['quantite'];
                        if ($produit->stock_actuel < 0) $produit->stock_actuel = 0;
                        $produit->save();
                    }
                }
            }

            // Mise à jour des lignes si fournies
            if ($request->has('lignes')) {
                $vente->lignesVente()->delete();
                $total = 0;
                foreach ($request->lignes as $ligne) {
                    LigneVente::create([
                        'vente_id' => $vente->id,
                        'produit_id' => $ligne['produit_id'],
                        'quantite' => $ligne['quantite'],
                        'prix_unitaire' => $ligne['prix_unitaire'],
                    ]);
                    $total += $ligne['quantite'] * $ligne['prix_unitaire'];
                }
                $vente->total = $total;
            }

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
