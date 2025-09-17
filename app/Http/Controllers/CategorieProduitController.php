<?php

namespace App\Http\Controllers;

use App\Models\CategorieProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class CategorieProduitController extends Controller
{
    public function index()
    {
        try {
            $categories = CategorieProduit::with(['entreprise', 'produits'])->get();
            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }

            $categorie = CategorieProduit::create([
                'entreprise_id' => $entreprise->id,
                'nom' => $request->nom,
                'description' => $request->description,
            ]);
            $categorie = CategorieProduit::with(['entreprise', 'produits'])->find($categorie->id);

            return response()->json(['message' => 'Catégorie créée avec succès', 'categorie' => $categorie], 201);
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

            $categorie = CategorieProduit::with(['entreprise', 'produits'])->find($id);
            if (!$categorie) {
                return response()->json(['message' => 'Catégorie non trouvée'], 404);
            }
            return response()->json(['categorie' => $categorie], 200);
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
                'description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $categorie = CategorieProduit::find($id);
            if (!$categorie) {
                return response()->json(['message' => 'Catégorie non trouvée'], 404);
            }

            $user = auth()->user();
            $entreprise = $user->entreprise;
            if (!$entreprise) {
                return response()->json(['message' => 'Entreprise non trouvée pour cet utilisateur'], 404);
            }

            $categorie->update(array_merge(
                $request->only(['nom', 'description']),
                ['entreprise_id' => $entreprise->id]
            ));
            $categorie = CategorieProduit::with(['entreprise', 'produits'])->find($categorie->id);

            return response()->json(['message' => 'Catégorie mise à jour', 'categorie' => $categorie], 200);
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

            $categorie = CategorieProduit::find($id);
            if (!$categorie) {
                return response()->json(['message' => 'Catégorie non trouvée'], 404);
            }
            $categorie->delete();
            return response()->json(['message' => 'Catégorie supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}