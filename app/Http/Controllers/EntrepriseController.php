<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Mail;

class EntrepriseController extends Controller
{
    public function index()
    {
        try {
            $entreprises = Entreprise::with(['user','abonnements', 'employeEntreprises'])->get();
            return response()->json(['entreprises' => $entreprises], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'raison_sociale' => 'required|string|max:255',
                'secteur_activite' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'telephone' => 'required|string|max:20',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'date_creation' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Création du User
            $verification_code = rand(100000, 999999);
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'client',
                'is_verified' => false,
                'verification_code' => $verification_code, // Ajoute ce champ dans la table users
            ]);

            // Création de l'Entreprise
            $entreprise = Entreprise::create([
                'user_id' => $user->id,
                'raison_sociale' => $request->raison_sociale,
                'secteur_activite' => $request->secteur_activite,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'email' => $request->email,
            ]);

            // Envoi du code de vérification par mail
            Mail::raw("Votre code de vérification est : $verification_code", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Code de vérification de votre compte');
            });

            $entreprise = Entreprise::with(['abonnements', 'employeEntreprises'])->find($entreprise->id);

            return response()->json(['message' => 'Entreprise créée, vérifiez votre email', 'entreprise' => $entreprise], 201);
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

            $entreprise = Entreprise::with(['user','abonnements', 'employeEntreprises'])->find($id);
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }
            return response()->json(['entreprise' => $entreprise], 200);
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
                'raison_sociale' => 'sometimes|required|string|max:255',
                'secteur_activite' => 'sometimes|required|string|max:255',
                'adresse' => 'sometimes|required|string|max:255',
                'telephone' => 'sometimes|required|string|max:20',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'sometimes|required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $entreprise = Entreprise::find($id);
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $entreprise->update($request->only([
                'raison_sociale', 'secteur_activite', 'adresse', 'telephone', 'email', 'date_creation', 'statut'
            ]));

            // Mise à jour du user associé si email ou password changent
            $user = $entreprise->user;
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $entreprise = Entreprise::with(['abonnements', 'employeEntreprises'])->find($entreprise->id);

            return response()->json(['message' => 'Entreprise mise à jour', 'entreprise' => $entreprise], 200);
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

            $entreprise = Entreprise::find($id);
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }
            $entreprise->user()->delete();
            $entreprise->delete();
            return response()->json(['message' => 'Entreprise supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    // Vérification du compte par code
    public function verify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'code' => 'required|digits:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user || $user->verification_code != $request->code) {
                return response()->json(['message' => 'Code incorrect ou utilisateur non trouvé'], 400);
            }

            $user->is_verified = true;
            $user->verification_code = null;
            $user->save();

            return response()->json(['message' => 'Compte vérifié avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}