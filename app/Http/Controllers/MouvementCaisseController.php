<?php

namespace App\Http\Controllers;

use App\Models\MouvementCaisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class MouvementCaisseController extends Controller
{
    public function index()
    {
        try {
            $mouvements = MouvementCaisse::with('employe')->get();
            return response()->json(['mouvements' => $mouvements], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'employe_id' => 'required|exists:employe_entreprises,id',
                'type_mouvement' => 'required|in:credit,debit',
                'montant' => 'required|numeric|min:0',
                'libelle' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:255',
                'date_mouvement' => 'required|date',
                'justificatif' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mouvement = MouvementCaisse::create($request->all());
            $mouvement = MouvementCaisse::with('employe')->find($mouvement->id);

            return response()->json(['message' => 'Mouvement créé avec succès', 'mouvement' => $mouvement], 201);
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

            $mouvement = MouvementCaisse::with('employe')->find($id);
            if (!$mouvement) {
                return response()->json(['message' => 'Mouvement non trouvé'], 404);
            }

            return response()->json(['mouvement' => $mouvement], 200);
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
                'employe_id' => 'sometimes|exists:employe_entreprises,id',
                'type_mouvement' => 'sometimes|in:credit,debit',
                'montant' => 'sometimes|numeric|min:0',
                'libelle' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:255',
                'date_mouvement' => 'sometimes|date',
                'justificatif' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $mouvement = MouvementCaisse::find($id);
            if (!$mouvement) {
                return response()->json(['message' => 'Mouvement non trouvé'], 404);
            }

            $mouvement->update($request->all());
            $mouvement = MouvementCaisse::with('employe')->find($mouvement->id);

            return response()->json(['message' => 'Mouvement mis à jour', 'mouvement' => $mouvement], 200);
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

            $mouvement = MouvementCaisse::find($id);
            if (!$mouvement) {
                return response()->json(['message' => 'Mouvement non trouvé'], 404);
            }

            $mouvement->delete();
            return response()->json(['message' => 'Mouvement supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
