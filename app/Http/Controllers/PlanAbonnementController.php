<?php

namespace App\Http\Controllers;

use App\Models\PlanAbonnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class PlanAbonnementController extends Controller
{
    public function index()
    {
        try {
            $plans = PlanAbonnement::with('abonnements')->get();
            return response()->json(['plans' => $plans], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'label' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'montant' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plan = PlanAbonnement::create($request->all());
            $plan = PlanAbonnement::with('abonnements')->find($plan->id);

            return response()->json(['message' => 'Plan créé avec succès', 'plan' => $plan], 201);
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

            $plan = PlanAbonnement::with('abonnements')->find($id);
            if (!$plan) {
                return response()->json(['message' => 'Plan non trouvé'], 404);
            }
            return response()->json(['plan' => $plan], 200);
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
                'label' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'montant' => 'sometimes|required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plan = PlanAbonnement::find($id);
            if (!$plan) {
                return response()->json(['message' => 'Plan non trouvé'], 404);
            }

            $plan->update($request->only(['label', 'description', 'montant']));
            $plan = PlanAbonnement::with('abonnements')->find($plan->id);

            return response()->json(['message' => 'Plan mis à jour', 'plan' => $plan], 200);
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

            $plan = PlanAbonnement::find($id);
            if (!$plan) {
                return response()->json(['message' => 'Plan non trouvé'], 404);
            }
            $plan->delete();
            return response()->json(['message' => 'Plan supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}