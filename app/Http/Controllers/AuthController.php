<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                // Générer un token d'authentification (exemple via Sanctum)
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Connexion réussie.',
                    'user' => $user,
                    'access_token' => 'Bearer ' . $token,
                    'token_type' => 'Bearer',
                ]);
            }

            return response()->json(['message' => 'Identifiants invalides.'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur.', 'error' => $e->getMessage()], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            Auth::logout();
            return response()->json(['message' => 'Déconnexion réussie.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur.', 'error' => $e->getMessage()], 500);
        }
    }
}
