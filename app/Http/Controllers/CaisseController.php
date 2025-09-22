<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class CaisseController extends Controller
{
    // Liste toutes les caisses avec leurs inventaires
    public function index()
    {
        try {
            $caisses = Caisse::with(['entreprise', 'inventaires'])->get();
            return response()->json(['caisses' => $caisses], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Créer une caisse
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom_caisse' => 'required|string|max:255',
                'type_caisse' => 'required|string|max:50',
                'solde_initial' => 'required|numeric|min:0',
                'solde_actuel' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $user = auth()->user();

            $caisse = Caisse::create([
                'entreprise_id' => $user->entreprise->id,
                'nom_caisse' => $request->nom_caisse,
                'type_caisse' => $request->type_caisse,
                'solde_initial' => $request->solde_initial,
                'solde_actuel' => $request->solde_actuel,
            ]);

            return response()->json(['message' => 'Caisse créée', 'caisse' => $caisse->load('inventaires')], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Afficher une caisse spécifique
    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $caisse = Caisse::with(['entreprise', 'inventaires'])->find($ids[0]);
            if (!$caisse) return response()->json(['message' => 'Caisse non trouvée'], 404);

            return response()->json(['caisse' => $caisse], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Mettre à jour une caisse
    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $caisse = Caisse::find($ids[0]);
            if (!$caisse) return response()->json(['message' => 'Caisse non trouvée'], 404);

            $validator = Validator::make($request->all(), [
                'nom_caisse' => 'sometimes|required|string|max:255',
                'type_caisse' => 'sometimes|required|string|max:50',
                'solde_initial' => 'sometimes|required|numeric|min:0',
                'solde_actuel' => 'sometimes|required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $caisse->update($request->only(['nom_caisse', 'type_caisse', 'solde_initial', 'solde_actuel']));

            return response()->json(['message' => 'Caisse mise à jour', 'caisse' => $caisse->load('inventaires')], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Supprimer une caisse
    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $caisse = Caisse::find($ids[0]);
            if (!$caisse) return response()->json(['message' => 'Caisse non trouvée'], 404);

            $caisse->delete();

            return response()->json(['message' => 'Caisse supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
