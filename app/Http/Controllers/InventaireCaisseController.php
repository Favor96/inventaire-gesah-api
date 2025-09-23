<?php

namespace App\Http\Controllers;

use App\Models\InventaireCaisse;
use App\Models\Caisse;
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
                'solde_reel' => 'required|numeric|min:0',
                'date_inventaire' => 'required|date',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
                'observations' => 'nullable|string',
                'statut' => 'nullable|string|in:en_cours,valide,annule',
                'lignes' => 'nullable|array',
                'lignes.*.type_billet' => 'required_with:lignes|string',
                'lignes.*.nombre' => 'required_with:lignes|integer|min:0',
                'lignes.*.montant' => 'required_with:lignes|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            // Récupérer le solde théorique depuis la caisse
            $caisse = Caisse::find($request->caisse_id);
            if (!$caisse) {
                return response()->json(['message' => 'Caisse non trouvée'], 404);
            }
            $solde_theorique = $caisse->solde_actuel;
            $ecart = $request->solde_reel - $solde_theorique;

            // Génération automatique du numéro d’inventaire
            $yearMonth = date('Y-m');
            $countThisMonth = InventaireCaisse::whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->count() + 1;
            $numero_inventaire = 'INV-' . $yearMonth . '-' . str_pad($countThisMonth, 3, '0', STR_PAD_LEFT);

            // Création de l’inventaire
            $inventaire = InventaireCaisse::create(array_merge(
                $request->only([
                    'entreprise_id',
                    'caisse_id',
                    'solde_reel',
                    'ecart',
                    'date_inventaire',
                    'date_debut',
                    'date_fin',
                    'observations',
                    'statut'
                ]),
                [
                    'agent_id' => $agent_id,
                    'numero_inventaire' => $numero_inventaire,
                    'solde_theorique' => $solde_theorique,
                    'ecart' => $ecart,
                ]
            ));

            // Création des lignes si présentes
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
                'solde_reel' => 'sometimes|numeric|min:0',
                'date_inventaire' => 'sometimes|date',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
                'observations' => 'nullable|string',
                'statut' => 'nullable|string|in:en_cours,valide,annule',
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

            // Si la caisse change, mettre à jour le solde théorique
            if ($request->has('caisse_id') && $request->caisse_id != $inventaire->caisse_id) {
                $caisse = Caisse::find($request->caisse_id);
                $solde_theorique = $caisse ? $caisse->solde_actuel : 0;
            } else {
                $solde_theorique = $inventaire->solde_theorique;
            }

            // Déterminer le solde réel pour calculer l'écart
            $solde_reel = $request->solde_reel ?? $inventaire->solde_reel;

            // Calcul automatique de l'écart
            $ecart = $solde_reel - $solde_theorique;

            // Mise à jour de l'inventaire
            $inventaire->update(array_merge(
                $request->only([
                    'entreprise_id',
                    'caisse_id',
                    'solde_reel',
                    'date_inventaire',
                    'date_debut',
                    'date_fin',
                    'observations',
                    'statut'
                ]),
                [
                    'agent_id' => $agent_id,
                    'solde_theorique' => $solde_theorique,
                    'ecart' => $ecart
                ]
            ));

            // Mise à jour des lignes
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
