<?php

namespace App\Http\Controllers;

use App\Models\AffectationImmo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class AffectationImmoController extends Controller
{
    // Liste toutes les affectations avec les relations
    public function index()
    {
        try {
            $affectations = AffectationImmo::with(['immobilisation', 'employe'])->get();
            return response()->json(['affectations' => $affectations], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Créer une affectation
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'immobilisation_id' => 'required|integer|exists:immobilisations,id',
                'employe_id' => 'required|integer|exists:employe_entreprises,id',
                'date_affectation' => 'required|date',
                'statut' => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $affectation = AffectationImmo::create($request->only(['immobilisation_id', 'employe_id', 'date_affectation', 'statut']));

            return response()->json(['message' => 'Affectation créée', 'affectation' => $affectation->load(['immobilisation', 'employe'])], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Afficher une affectation spécifique
    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $affectation = AffectationImmo::with(['immobilisation', 'employe'])->find($ids[0]);
            if (!$affectation) return response()->json(['message' => 'Affectation non trouvée'], 404);

            return response()->json(['affectation' => $affectation], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Mettre à jour une affectation
    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $affectation = AffectationImmo::find($ids[0]);
            if (!$affectation) return response()->json(['message' => 'Affectation non trouvée'], 404);

            $validator = Validator::make($request->all(), [
                'immobilisation_id' => 'sometimes|required|integer|exists:immobilisations,id',
                'employe_id' => 'sometimes|required|integer|exists:employe_entreprises,id',
                'date_affectation' => 'sometimes|required|date',
                'statut' => 'sometimes|required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $affectation->update($request->only(['immobilisation_id', 'employe_id', 'date_affectation', 'statut']));

            return response()->json(['message' => 'Affectation mise à jour', 'affectation' => $affectation->load(['immobilisation', 'employe'])], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Supprimer une affectation
    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $affectation = AffectationImmo::find($ids[0]);
            if (!$affectation) return response()->json(['message' => 'Affectation non trouvée'], 404);

            $affectation->delete();

            return response()->json(['message' => 'Affectation supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
