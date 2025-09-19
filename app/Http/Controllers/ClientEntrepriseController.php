<?php

namespace App\Http\Controllers;

use App\Models\ClientEntreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class ClientEntrepriseController extends Controller
{
    public function index()
    {
        try {
            $clients = ClientEntreprise::with('entreprise')->get();
            return response()->json(['clients' => $clients], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'telephone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer entreprise_id depuis utilisateur connecté
            $entrepriseId = auth()->user()->entreprise->id ?? null;
            if (!$entrepriseId) {
                return response()->json(['message' => 'Entreprise introuvable pour utilisateur connecté'], 403);
            }

            $client = ClientEntreprise::create(array_merge($request->all(), [
                'entreprise_id' => $entrepriseId,
            ]));

            $client = ClientEntreprise::with('entreprise')->find($client->id);

            return response()->json(['message' => 'Client créé avec succès', 'client' => $client], 201);
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

            $client = ClientEntreprise::with('entreprise')->find($id);
            if (!$client) {
                return response()->json(['message' => 'Client non trouvé'], 404);
            }
            return response()->json(['client' => $client], 200);
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
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'telephone' => 'sometimes|required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $client = ClientEntreprise::find($id);
            if (!$client) {
                return response()->json(['message' => 'Client non trouvé'], 404);
            }

            $client->update($request->all());
            $client = ClientEntreprise::with('entreprise')->find($client->id);

            return response()->json(['message' => 'Client mis à jour', 'client' => $client], 200);
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

            $client = ClientEntreprise::find($id);
            if (!$client) {
                return response()->json(['message' => 'Client non trouvé'], 404);
            }
            $client->delete();
            return response()->json(['message' => 'Client supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
