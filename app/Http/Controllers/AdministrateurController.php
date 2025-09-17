<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use App\Models\User;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids as FacadesHashids;

class AdministrateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $admins = Administrateur::with('user')->get();
            return response()->json(['administrateurs' => $admins],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:4',
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
                'role' => 'admin',
                'is_verified' => true, 
            ]);

            // Création de l'Administrateur
            $admin = Administrateur::create([
                'user_id' => $user->id,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
            ]);
            $admin = Administrateur::with('user')->find($admin->id);
            return response()->json([
                'message' => 'Administrateur créé avec succès',
                'administrateur' => $admin,
            ],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $hashid)
    {
        try {
            $ids = FacadesHashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $admin = Administrateur::with('user')->find($id);
            if (!$admin) {
                return response()->json(['message' => 'Administrateur non trouvé'], 404);
            }
            return response()->json(['administrateur' => $admin], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $hashid)
    {
        try {
            $ids = FacadesHashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:4',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
        
            $admin = Administrateur::find($id);
            if (!$admin) {
                return response()->json(['message' => 'Administrateur non trouvé'], 404);
            }

            // Mise à jour des champs administrateur
            $admin->update($request->only(['nom', 'prenom', 'email']));

            // Mise à jour du user associé si email ou password changent
            $user = $admin->user;
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $admin = Administrateur::with('user')->find($admin->id);
            return response()->json(['message' => 'Administrateur mis à jour', 'administrateur' => $admin]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $admin = Administrateur::find($id);
            if (!$admin) {
                return response()->json(['message' => 'Administrateur non trouvé'], 404);
            }
            // Supprime le user associé
            $admin->user()->delete();
            // Supprime l'administrateur
            $admin->delete();
            return response()->json(['message' => 'Administrateur supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
