<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class FournisseurController extends Controller
{
    public function index()
    {
        try {
            $fournisseurs = Fournisseur::with(['entreprise', 'achats'])->get();
            return response()->json(['fournisseurs' => $fournisseurs], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'telephone' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:fournisseurs,email',
                'numero_fiscal' => 'nullable|string|max:100',            
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

            $fournisseur = Fournisseur::create([
                'entreprise_id' => $entreprise->id,
                'nom' => $request->nom,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'numero_fiscal' => $request->numero_fiscal,
            ]);
            $fournisseur = Fournisseur::with(['entreprise', 'achats'])->find($fournisseur->id);

            return response()->json(['message' => 'Fournisseur créé avec succès', 'fournisseur' => $fournisseur], 201);
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

            $fournisseur = Fournisseur::with(['entreprise', 'achats'])->find($id);
            if (!$fournisseur) {
                return response()->json(['message' => 'Fournisseur non trouvé'], 404);
            }
            return response()->json(['fournisseur' => $fournisseur], 200);
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
                'adresse' => 'sometimes|required|string|max:255',
                'telephone' => 'sometimes|required|string|max:20',
                'email' => 'sometimes|required|email|max:255|unique:fournisseurs,email,' . $id,
                'numero_fiscal' => 'sometimes|required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fournisseur = Fournisseur::find($id);
            if (!$fournisseur) {
                return response()->json(['message' => 'Fournisseur non trouvé'], 404);
            }

            // Récupère l'entreprise liée à l'utilisateur connecté
            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }

            $fournisseur->update(array_merge(
                $request->only(['nom', 'adresse', 'telephone', 'email', 'numero_fiscal', 'date_creation']),
                ['entreprise_id' => $entreprise->id]
            ));
            $fournisseur = Fournisseur::with(['entreprise', 'achats'])->find($fournisseur->id);

            return response()->json(['message' => 'Fournisseur mis à jour', 'fournisseur' => $fournisseur], 200);
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

            $fournisseur = Fournisseur::find($id);
            if (!$fournisseur) {
                return response()->json(['message' => 'Fournisseur non trouvé'], 404);
            }
            $fournisseur->delete();
            return response()->json(['message' => 'Fournisseur supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}