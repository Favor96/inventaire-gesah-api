<?php

namespace App\Http\Controllers;

use App\Models\InventaireCaisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class InventaireCaisseController extends Controller
{
    public function index()
    {
        try {
            $inventaires = InventaireCaisse::with(['agent', 'caisse', 'lignes'])->get();
            return response()->json(['inventaires' => $inventaires], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $agent_id = $user->agent->id ?? null;
            if (!$agent_id) {
                return response()->json(['message' => 'Agent non trouvé pour l’utilisateur connecté'], 404);
            }

            $validator = Validator::make($request->all(), [
                'entreprise_id' => 'required|exists:entreprises,id',
                'caisse_id' => 'required|exists:caisses,id',
                'numero_inventaire' => 'required|string|unique:inventaire_caisses,numero_inventaire',
                'solde_theorique' => 'required|numeric|min:0',
                'solde_reel' => 'required|numeric|min:0',
                'date_inventaire' => 'required|date',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
                'observations' => 'nullable|string',
                'statut' => 'nullable|string',
                'lignes' => 'nullable|array',
                'lignes.*.type_billet' => 'required_with:lignes|string',
                'lignes.*.nombre' => 'required_with:lignes|integer|min:0',
                'lignes.*.montant' => 'required_with:lignes|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $inventaire = InventaireCaisse::create(array_merge(
                $request->only([
                    'entreprise_id',
                    'caisse_id',
                    'numero_inventaire',
                    'solde_theorique',
                    'solde_reel',
                    'ecart',
                    'date_inventaire',
                    'date_debut',
                    'date_fin',
                    'observations',
                    'statut'
                ]),
                ['agent_id' => $agent_id]
            ));

            if ($request->has('lignes')) {
                foreach ($request->lignes as $ligne) {
                    $inventaire->lignes()->create($ligne);
                }
            }

            $inventaire = InventaireCaisse::with(['agent', 'caisse', 'lignes'])->find($inventaire->id);

            return response()->json(['message' => 'Inventaire créé avec succès', 'inventaire' => $inventaire], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $inventaire = InventaireCaisse::with(['agent', 'caisse', 'lignes'])->find($ids[0]);
            if (!$inventaire) return response()->json(['message' => 'Inventaire non trouvé'], 404);

            return response()->json(['inventaire' => $inventaire], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $user = auth()->user();
            $agent_id = $user->agent->id ?? null;
            if (!$agent_id) {
                return response()->json(['message' => 'Agent non trouvé pour l’utilisateur connecté'], 404);
            }

            $validator = Validator::make($request->all(), [
                'entreprise_id' => 'sometimes|exists:entreprises,id',
                'caisse_id' => 'sometimes|exists:caisses,id',
                'numero_inventaire' => 'sometimes|string|unique:inventaire_caisses,numero_inventaire,' . $ids[0],
                'solde_theorique' => 'sometimes|numeric|min:0',
                'solde_reel' => 'sometimes|numeric|min:0',
                'date_inventaire' => 'sometimes|date',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
                'observations' => 'nullable|string',
                'statut' => 'nullable|string',
                'lignes' => 'nullable|array',
                'lignes.*.type_billet' => 'required_with:lignes|string',
                'lignes.*.nombre' => 'required_with:lignes|integer|min:0',
                'lignes.*.montant' => 'required_with:lignes|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $inventaire = InventaireCaisse::find($ids[0]);
            if (!$inventaire) return response()->json(['message' => 'Inventaire non trouvé'], 404);

            $inventaire->update(array_merge(
                $request->only([
                    'entreprise_id',
                    'caisse_id',
                    'numero_inventaire',
                    'solde_theorique',
                    'solde_reel',
                    'ecart',
                    'date_inventaire',
                    'date_debut',
                    'date_fin',
                    'observations',
                    'statut'
                ]),
                ['agent_id' => $agent_id]
            ));

            if ($request->has('lignes')) {
                $inventaire->lignes()->delete();
                foreach ($request->lignes as $ligne) {
                    $inventaire->lignes()->create($ligne);
                }
            }

            $inventaire = InventaireCaisse::with(['agent', 'caisse', 'lignes'])->find($inventaire->id);

            return response()->json(['message' => 'Inventaire mis à jour', 'inventaire' => $inventaire], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $inventaire = InventaireCaisse::find($ids[0]);
            if (!$inventaire) return response()->json(['message' => 'Inventaire non trouvé'], 404);

            $inventaire->delete();

            return response()->json(['message' => 'Inventaire supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
