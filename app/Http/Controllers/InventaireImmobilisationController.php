<?php

namespace App\Http\Controllers;

use App\Models\InventaireImmobilisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class InventaireImmobilisationController extends Controller
{
    // Liste tous les inventaires avec leurs lignes
    public function index()
    {
        try {
            $inventaires = InventaireImmobilisation::with(['agent', 'entreprise', 'lignes.immobilisation'])->get();
            return response()->json(['inventaires' => $inventaires], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Créer un inventaire avec ses lignes
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'numero_inventaire' => 'required|string|max:100',
                'date_inventaire' => 'required|date',
                'statut' => 'required|string|max:50',
                'observations' => 'nullable|string',
                'lignes' => 'nullable|array',
                'lignes.*.immobilisation_id' => 'required|integer|exists:immobilisations,id',
                'lignes.*.etat_constate' => 'required|string|max:255',
                'lignes.*.localisation_constatee' => 'nullable|string|max:255',
                'lignes.*.valeur_estimee' => 'required|numeric|min:0',
                'lignes.*.observations' => 'nullable|string',
                'lignes.*.present' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $user = auth()->user();
            $agent_id = $user->agent->id ?? null;
            if (!$agent_id) return response()->json(['message' => 'Agent non trouvé'], 404);

            $inventaire = InventaireImmobilisation::create([
                'entreprise_id' => $user->entreprise_id,
                'agent_id' => $agent_id,
                'numero_inventaire' => $request->numero_inventaire,
                'date_inventaire' => $request->date_inventaire,
                'statut' => $request->statut,
                'observations' => $request->observations,
            ]);

            if ($request->has('lignes')) {
                foreach ($request->lignes as $ligne) {
                    $inventaire->lignes()->create($ligne);
                }
            }

            return response()->json(['message' => 'Inventaire créé', 'inventaire' => $inventaire->load('lignes')], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Afficher un inventaire spécifique avec ses lignes
    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $inventaire = InventaireImmobilisation::with(['agent', 'entreprise', 'lignes.immobilisation'])->find($ids[0]);
            if (!$inventaire) return response()->json(['message' => 'Inventaire non trouvé'], 404);

            return response()->json(['inventaire' => $inventaire], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Mettre à jour un inventaire et ses lignes
    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $inventaire = InventaireImmobilisation::find($ids[0]);
            if (!$inventaire) return response()->json(['message' => 'Inventaire non trouvé'], 404);

            $validator = Validator::make($request->all(), [
                'numero_inventaire' => 'sometimes|required|string|max:100',
                'date_inventaire' => 'sometimes|required|date',
                'statut' => 'sometimes|required|string|max:50',
                'observations' => 'nullable|string',
                'lignes' => 'nullable|array',
                'lignes.*.immobilisation_id' => 'required|integer|exists:immobilisations,id',
                'lignes.*.etat_constate' => 'required|string|max:255',
                'lignes.*.localisation_constatee' => 'nullable|string|max:255',
                'lignes.*.valeur_estimee' => 'required|numeric|min:0',
                'lignes.*.observations' => 'nullable|string',
                'lignes.*.present' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $inventaire->update($request->only(['numero_inventaire', 'date_inventaire', 'statut', 'observations']));

            if ($request->has('lignes')) {
                $inventaire->lignes()->delete(); // Supprimer les anciennes lignes
                foreach ($request->lignes as $ligne) {
                    $inventaire->lignes()->create($ligne);
                }
            }

            return response()->json(['message' => 'Inventaire mis à jour', 'inventaire' => $inventaire->load('lignes')], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Supprimer un inventaire et ses lignes
    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $inventaire = InventaireImmobilisation::find($ids[0]);
            if (!$inventaire) return response()->json(['message' => 'Inventaire non trouvé'], 404);

            $inventaire->lignes()->delete(); // Supprimer toutes les lignes
            $inventaire->delete();

            return response()->json(['message' => 'Inventaire supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
