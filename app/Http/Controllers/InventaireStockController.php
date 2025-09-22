<?php

namespace App\Http\Controllers;

use App\Models\InventaireStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class InventaireStockController extends Controller
{
    /**
     * Afficher la liste des inventaires
     */
    public function index()
    {
        try {
            $inventaires = InventaireStock::with(['entreprise', 'agent', 'lignes.produit'])
                ->latest()
                ->get();

            return response()->json($inventaires, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouvel inventaire avec ses lignes
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entreprise_id' => 'required|exists:entreprises,id',
                'numero_inventaire' => 'required|string|max:50|unique:inventaire_stocks,numero_inventaire',
                'date_inventaire' => 'required|date',
                'statut' => 'required|string|max:50',
                'lignes' => 'required|array|min:1',
                'lignes.*.produit_id' => 'required|exists:produits,id',
                'lignes.*.quantite_reelle' => 'required|integer|min:0',
                'lignes.*.observations' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors'  => $validator->errors()
                ], 422);
            }
dd(auth()->user()->agent);
            $inventaire = InventaireStock::create([
                'entreprise_id'     => $request->entreprise_id,
                'agent_id'          => auth()->user()->agent->id,
                'numero_inventaire' => $request->numero_inventaire,
                'date_inventaire'   => $request->date_inventaire,
                'statut'            => $request->statut,
            ]);

            foreach ($request->lignes as $ligne) {
                $inventaire->lignes()->create([
                    'produit_id'      => $ligne['produit_id'],
                    'quantite_reelle' => $ligne['quantite_reelle'],
                    'observations'    => $ligne['observations'] ?? null,
                ]);
            }

            return response()->json([
                'message'    => 'Inventaire créé avec succès',
                'inventaire' => $inventaire->load(['lignes.produit'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un inventaire spécifique
     */
    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $inventaire = InventaireStock::with(['entreprise', 'agent', 'lignes.produit'])->find($id);

            if (!$inventaire) {
                return response()->json(['message' => 'Inventaire non trouvé'], 404);
            }

            return response()->json($inventaire, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un inventaire
     */
    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $inventaire = InventaireStock::find($id);
            if (!$inventaire) {
                return response()->json(['message' => 'Inventaire non trouvé'], 404);
            }

            $validator = Validator::make($request->all(), [
                'statut' => 'sometimes|required|string|max:50',
                'date_validation' => 'nullable|date',
                'lignes' => 'nullable|array|min:1',
                'lignes.*.produit_id' => 'required_with:lignes|exists:produits,id',
                'lignes.*.quantite_reelle' => 'required_with:lignes|integer|min:0',
                'lignes.*.observations' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $inventaire->update($request->only(['statut', 'date_validation']));

            if ($request->has('lignes')) {
                $inventaire->lignes()->delete(); // on remplace toutes les lignes
                foreach ($request->lignes as $ligne) {
                    $inventaire->lignes()->create([
                        'produit_id'      => $ligne['produit_id'],
                        'quantite_reelle' => $ligne['quantite_reelle'],
                        'observations'    => $ligne['observations'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'message'    => 'Inventaire mis à jour',
                'inventaire' => $inventaire->load(['lignes.produit'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un inventaire
     */
    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $inventaire = InventaireStock::find($id);
            if (!$inventaire) {
                return response()->json(['message' => 'Inventaire non trouvé'], 404);
            }

            $inventaire->lignes()->delete();
            $inventaire->delete();

            return response()->json(['message' => 'Inventaire supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
