<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class AbonnementController extends Controller
{
    public function index()
    {
        try {
            $abonnements = Abonnement::with(['entreprise', 'plan'])->get();
            return response()->json(['abonnements' => $abonnements], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entreprise_id' => 'required|exists:entreprises,id',
                'plan_id' => 'required|exists:plan_abonnements,id',
                'montant_mensuel' => 'required|numeric|min:0',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'statut' => 'required|string|max:50',
                'date_paiement' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $abonnement = Abonnement::create($request->all());
            $abonnement = Abonnement::with(['entreprise', 'plan'])->find($abonnement->id);

            return response()->json(['message' => 'Abonnement créé avec succès', 'abonnement' => $abonnement], 201);
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

            $abonnement = Abonnement::with(['entreprise', 'plan'])->find($id);
            if (!$abonnement) {
                return response()->json(['message' => 'Abonnement non trouvé'], 404);
            }
            return response()->json(['abonnement' => $abonnement], 200);
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
                'entreprise_id' => 'sometimes|required|exists:entreprises,id',
                'plan_id' => 'sometimes|required|exists:plan_abonnements,id',
                'montant_mensuel' => 'sometimes|required|numeric|min:0',
                'date_debut' => 'sometimes|required|date',
                'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
                'statut' => 'sometimes|required|string|max:50',
                'date_paiement' => 'sometimes|required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $abonnement = Abonnement::find($id);
            if (!$abonnement) {
                return response()->json(['message' => 'Abonnement non trouvé'], 404);
            }

            $abonnement->update($request->all());
            $abonnement = Abonnement::with(['entreprise', 'plan'])->find($abonnement->id);

            return response()->json(['message' => 'Abonnement mis à jour', 'abonnement' => $abonnement], 200);
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

            $abonnement = Abonnement::find($id);
            if (!$abonnement) {
                return response()->json(['message' => 'Abonnement non trouvé'], 404);
            }
            $abonnement->delete();
            return response()->json(['message' => 'Abonnement supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
