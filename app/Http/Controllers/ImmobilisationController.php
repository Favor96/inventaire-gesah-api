<?php

namespace App\Http\Controllers;

use App\Models\Immobilisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class ImmobilisationController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }
            $immobilisations = Immobilisation::where('entreprise_id', $entreprise->id)->get();
            return response()->json(['immobilisations' => $immobilisations], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type_id' => 'required|exists:type_immobilisations,id',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'numero_serie' => 'nullable|string|max:255',
                'valeur_acquisition' => 'required|numeric|min:0',
                'date_acquisition' => 'required|date',
                'date_mise_service' => 'nullable|date',
                'etat' => 'nullable|string|max:100',
                'localisation' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }

            $immobilisation = Immobilisation::create([
                'entreprise_id' => $entreprise->id,
                'type_id' => $request->type_id,
                'nom' => $request->nom,
                'description' => $request->description,
                'numero_serie' => $request->numero_serie,
                'valeur_acquisition' => $request->valeur_acquisition,
                'date_acquisition' => $request->date_acquisition,
                'date_mise_service' => $request->date_mise_service,
                'etat' => $request->etat,
                'localisation' => $request->localisation,
            ]);

            return response()->json(['message' => 'Immobilisation créée avec succès', 'immobilisation' => $immobilisation], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $immobilisation = Immobilisation::find($id);
            if (!$immobilisation) {
                return response()->json(['message' => 'Immobilisation non trouvée'], 404);
            }
            return response()->json(['immobilisation' => $immobilisation], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $validator = Validator::make($request->all(), [
                'type_id' => 'sometimes|required|exists:type_immobilisations,id',
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'numero_serie' => 'nullable|string|max:255',
                'valeur_acquisition' => 'sometimes|required|numeric|min:0',
                'date_acquisition' => 'sometimes|required|date',
                'date_mise_service' => 'nullable|date',
                'etat' => 'nullable|string|max:100',
                'localisation' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $immobilisation = Immobilisation::find($id);
            if (!$immobilisation) {
                return response()->json(['message' => 'Immobilisation non trouvée'], 404);
            }

            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }

            $immobilisation->update(array_merge(
                $request->only([
                    'type_id',
                    'nom',
                    'description',
                    'numero_serie',
                    'valeur_acquisition',
                    'date_acquisition',
                    'date_mise_service',
                    'etat',
                    'localisation'
                ]),
                ['entreprise_id' => $entreprise->id]
            ));

            return response()->json(['message' => 'Immobilisation mise à jour', 'immobilisation' => $immobilisation], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $immobilisation = Immobilisation::find($id);
            if (!$immobilisation) {
                return response()->json(['message' => 'Immobilisation non trouvée'], 404);
            }
            $immobilisation->delete();
            return response()->json(['message' => 'Immobilisation supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
