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
            $inventaires = InventaireStock::with(['entreprise', 'agent', 'lignes.produitPackage'])
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
     * Générer un numéro d’inventaire unique
     */
    private function generateNumeroInventaire(): string
    {
        $prefix = 'INV-' . date('Y-m') . '-';

        // Récupérer le dernier inventaire de ce mois
        $lastInventaire = InventaireStock::where('numero_inventaire', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;
        if ($lastInventaire) {
            $parts = explode('-', $lastInventaire->numero_inventaire);
            $lastNumber = intval(end($parts));
        }

        return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Créer un nouvel inventaire avec ses lignes
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entreprise_id' => 'required|exists:entreprises,id',
                'date_inventaire' => 'required|date',
                'statut' => 'required|in:en_cours,valide,annule',
                'lignes' => 'required|array|min:1',
                'lignes.*.produit_package_id' => 'required|exists:produit_packages,id',
                'lignes.*.quantite_reelle' => 'required|integer|min:0',
                'lignes.*.observations' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $numeroInventaire = $this->generateNumeroInventaire();

            $inventaire = InventaireStock::create([
                'entreprise_id'     => $request->entreprise_id,
                'agent_id'          => auth()->user()->agent->id,
                'numero_inventaire' => $numeroInventaire,
                'date_inventaire'   => $request->date_inventaire,
                'statut'            => $request->statut,
            ]);

            foreach ($request->lignes as $ligne) {
                $inventaire->lignes()->create([
                    'produit_package_id' => $ligne['produit_package_id'],
                    'quantite_reelle'    => $ligne['quantite_reelle'],
                    'observations'       => $ligne['observations'] ?? null,
                ]);
            }

            return response()->json([
                'message'    => 'Inventaire créé avec succès',
                'inventaire' => $inventaire->load(['lignes.produitPackage'])
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

            $inventaire = InventaireStock::with(['entreprise', 'agent', 'lignes.produitPackage'])->find($id);

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
                'statut' => 'sometimes|required|in:en_cours,valide,annule',
                'date_validation' => 'nullable|date',
                'lignes' => 'nullable|array|min:1',
                'lignes.*.produit_package_id' => 'required_with:lignes|exists:produit_packages,id',
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
                        'produit_package_id' => $ligne['produit_package_id'],
                        'quantite_reelle'    => $ligne['quantite_reelle'],
                        'observations'       => $ligne['observations'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'message'    => 'Inventaire mis à jour',
                'inventaire' => $inventaire->load(['lignes.produitPackage'])
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
