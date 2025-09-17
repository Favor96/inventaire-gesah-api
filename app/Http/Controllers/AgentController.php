<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class AgentController extends Controller
{
    public function index()
    {
        try {
            $agents = Agent::with('chef')->get();
            return response()->json(['agents' => $agents], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'chef_id' => 'required|exists:chef_missions,id',
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'telephone' => 'required|string|max:20',
                'specialite' => 'required|string|max:255',
                'password' => 'required|string|min:6',
                'actif' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'agent',
                'is_verified' => true,
            ]);

            $agent = Agent::create([
                'user_id' => $user->id,
                'chef_id' => $request->chef_id,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'specialite' => $request->specialite,
                'actif' => $request->actif,
            ]);

            $agent = Agent::with('chef')->find($agent->id);

            return response()->json(['message' => 'Agent créé avec succès', 'agent' => $agent], 201);
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

            $agent = Agent::with('chef')->find($id);
            if (!$agent) {
                return response()->json(['message' => 'Agent non trouvé'], 404);
            }
            return response()->json(['agent' => $agent], 200);
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
                'chef_id' => 'sometimes|required|exists:chef_missions,id',
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'telephone' => 'sometimes|required|string|max:20',
                'specialite' => 'sometimes|required|string|max:255',
                'password' => 'sometimes|required|string|min:6',
                'actif' => 'sometimes|required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $agent = Agent::find($id);
            if (!$agent) {
                return response()->json(['message' => 'Agent non trouvé'], 404);
            }

            $agent->update($request->only([
                'chef_id', 'nom', 'prenom', 'email', 'telephone', 'specialite', 'actif'
            ]));

            $user = $agent->user;
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $agent = Agent::with('chef')->find($agent->id);

            return response()->json(['message' => 'Agent mis à jour', 'agent' => $agent], 200);
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

            $agent = Agent::find($id);
            if (!$agent) {
                return response()->json(['message' => 'Agent non trouvé'], 404);
            }
            $agent->user()->delete();
            $agent->delete();
            return response()->json(['message' => 'Agent supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}