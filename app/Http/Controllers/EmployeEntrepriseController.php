<?php

namespace App\Http\Controllers;

use App\Models\EmployeEntreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class EmployeEntrepriseController extends Controller
{
    public function index()
    {
        try {
            $employes = EmployeEntreprise::with('entreprise')->get();
            return response()->json(['employes' => $employes], 200);
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
                'poste' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'telephone' => 'required|string|max:20',
                'date_creation' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupère l'entreprise liée à l'utilisateur connecté
            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }

            $employe = EmployeEntreprise::create([
                'entreprise_id' => $entreprise->id,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'poste' => $request->poste,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'date_creation' => $request->date_creation,
            ]);
            $employe = EmployeEntreprise::with('entreprise')->find($employe->id);

            return response()->json(['message' => 'Employé créé avec succès', 'employe' => $employe], 201);
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

            $employe = EmployeEntreprise::with('entreprise')->find($id);
            if (!$employe) {
                return response()->json(['message' => 'Employé non trouvé'], 404);
            }
            return response()->json(['employe' => $employe], 200);
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
                'poste' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'telephone' => 'sometimes|required|string|max:20',
                'date_creation' => 'sometimes|required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $employe = EmployeEntreprise::find($id);
            if (!$employe) {
                return response()->json(['message' => 'Employé non trouvé'], 404);
            }

            // Récupère l'entreprise liée à l'utilisateur connecté
            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }

            $employe->update(array_merge(
                $request->only(['nom', 'prenom', 'poste', 'email', 'telephone', 'date_creation']),
                ['entreprise_id' => $entreprise->id]
            ));
            $employe = EmployeEntreprise::with('entreprise')->find($employe->id);

            return response()->json(['message' => 'Employé mis à jour', 'employe' => $employe], 200);
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

            $employe = EmployeEntreprise::find($id);
            if (!$employe) {
                return response()->json(['message' => 'Employé non trouvé'], 404);
            }
            $employe->delete();
            return response()->json(['message' => 'Employé supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}