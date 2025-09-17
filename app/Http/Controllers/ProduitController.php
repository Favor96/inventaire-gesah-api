<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class ProduitController extends Controller
{
    public function index()
    {
        try {
            $produits = Produit::with(['entreprise', 'categorie', 'lignesVente', 'lignesAchat'])->get();
            return response()->json(['produits' => $produits], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entreprise_id' => 'required|exists:entreprises,id',
                'categorie_id' => 'required|exists:categorie_produits,id',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'code_produit' => 'required|string|max:50|unique:produits,code_produit',
                'prix_unitaire' => 'required|numeric|min:0',
                'stock_minimum' => 'required|integer|min:0',
                'stock_actuel' => 'required|integer|min:0',
                'unite_mesure' => 'required|string|max:50',
                'date_creation' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $produit = Produit::create($request->all());
            $produit = Produit::with(['entreprise', 'categorie', 'lignesVente', 'lignesAchat'])->find($produit->id);

            return response()->json(['message' => 'Produit créé avec succès', 'produit' => $produit], 201);
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

            $produit = Produit::with(['entreprise', 'categorie', 'lignesVente', 'lignesAchat'])->find($id);
            if (!$produit) {
                return response()->json(['message' => 'Produit non trouvé'], 404);
            }
            return response()->json(['produit' => $produit], 200);
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
                'entreprise_id' => 'sometimes|required|exists:entreprises,id',
                'categorie_id' => 'sometimes|required|exists:categorie_produits,id',
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'code_produit' => 'sometimes|required|string|max:50|unique:produits,code_produit,' . $id,
                'prix_unitaire' => 'sometimes|required|numeric|min:0',
                'stock_minimum' => 'sometimes|required|integer|min:0',
                'stock_actuel' => 'sometimes|required|integer|min:0',
                'unite_mesure' => 'sometimes|required|string|max:50',
                'date_creation' => 'sometimes|required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $produit = Produit::find($id);
            if (!$produit) {
                return response()->json(['message' => 'Produit non trouvé'], 404);
            }

            $produit->update($request->all());
            $produit = Produit::with(['entreprise', 'categorie', 'lignesVente', 'lignesAchat'])->find($produit->id);

            return response()->json(['message' => 'Produit mis à jour', 'produit' => $produit], 200);
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

            $produit = Produit::find($id);
            if (!$produit) {
                return response()->json(['message' => 'Produit non trouvé'], 404);
            }
            $produit->delete();
            return response()->json(['message' => 'Produit supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}