<?php

namespace App\Http\Controllers;

use App\Models\DetentionImmo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class DetentionImmoController extends Controller
{
    // Liste toutes les détentions avec les relations
    public function index()
    {
        try {
            $detentions = DetentionImmo::with(['immobilisation', 'employe'])->get();
            return response()->json(['detentions' => $detentions], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Créer une détention
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'immobilisation_id' => 'required|integer|exists:immobilisations,id',
                'employe_id' => 'required|integer|exists:employe_entreprises,id',
                'date_debut' => 'required|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'statut' => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $detention = DetentionImmo::create($request->only(['immobilisation_id', 'employe_id', 'date_debut', 'date_fin', 'statut']));

            return response()->json(['message' => 'Détention créée', 'detention' => $detention->load(['immobilisation', 'employe'])], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Afficher une détention spécifique
    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $detention = DetentionImmo::with(['immobilisation', 'employe'])->find($ids[0]);
            if (!$detention) return response()->json(['message' => 'Détention non trouvée'], 404);

            return response()->json(['detention' => $detention], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Mettre à jour une détention
    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $detention = DetentionImmo::find($ids[0]);
            if (!$detention) return response()->json(['message' => 'Détention non trouvée'], 404);

            $validator = Validator::make($request->all(), [
                'immobilisation_id' => 'sometimes|required|integer|exists:immobilisations,id',
                'employe_id' => 'sometimes|required|integer|exists:employe_entreprises,id',
                'date_debut' => 'sometimes|required|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'statut' => 'sometimes|required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $detention->update($request->only(['immobilisation_id', 'employe_id', 'date_debut', 'date_fin', 'statut']));

            return response()->json(['message' => 'Détention mise à jour', 'detention' => $detention->load(['immobilisation', 'employe'])], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Supprimer une détention
    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $detention = DetentionImmo::find($ids[0]);
            if (!$detention) return response()->json(['message' => 'Détention non trouvée'], 404);

            $detention->delete();

            return response()->json(['message' => 'Détention supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
