<?php

namespace App\Http\Controllers;

use App\Models\ChefMission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class ChefMissionController extends Controller
{
    public function index()
    {
        try {
            $chefs = ChefMission::with(['administrateur', 'agents'])->get();
            return response()->json(['chefs' => $chefs], 200);
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
                'email' => 'required|email|unique:users,email',
                'telephone' => 'required|string|max:20',
                'poste' => 'required|string|max:255',
                'password' => 'required|string|min:6',
                'actif' => 'required|boolean',
                'date_embauche' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Création du User
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'chef_de_mission',
                'is_verified' => true,
            ]);

            // Création du ChefMission
            $chef = ChefMission::create([
                'user_id' => $user->id,
                'administrateur_id' => Auth()->user()->administrateur->id,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'poste' => $request->poste,
                'actif' => $request->actif,
            ]);

            $chef = ChefMission::with(['administrateur', 'agents'])->find($chef->id);

            return response()->json(['message' => 'Chef de mission créé avec succès', 'chef' => $chef], 201);
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

            $chef = ChefMission::with(['administrateur', 'agents','user'])->find($id);
            if (!$chef) {
                return response()->json(['message' => 'Chef de mission non trouvé'], 404);
            }
            return response()->json(['chef' => $chef], 200);
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
                'administrateur_id' => 'sometimes|required|exists:administrateurs,id',
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'telephone' => 'sometimes|required|string|max:20',
                'poste' => 'sometimes|required|string|max:255',
                'password' => 'sometimes|required|string|min:6',
                'actif' => 'sometimes|required|boolean',
                'date_embauche' => 'sometimes|required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $chef = ChefMission::find($id);
            if (!$chef) {
                return response()->json(['message' => 'Chef de mission non trouvé'], 404);
            }

            $chef->update($request->only([
                'administrateur_id', 'nom', 'prenom', 'email', 'telephone', 'poste', 'actif'
            ]));

            // Mise à jour du user associé si email ou password changent
            $user = $chef->user;
            if ($user) {
                if ($request->has('email')) {
                    $user->email = $request->email;
                }
                if ($request->has('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();
            }


            $chef->load(['user', 'agents']);
            return response()->json(['message' => 'Chef de mission mis à jour', 'chef' => $chef], 200);
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

            $chef = ChefMission::find($id);
            if (!$chef) {
                return response()->json(['message' => 'Chef de mission non trouvé'], 404);
            }
            $chef->user()->delete();
            $chef->delete();
            return response()->json(['message' => 'Chef de mission supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}