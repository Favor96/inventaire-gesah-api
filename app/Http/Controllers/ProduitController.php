<?Php

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
            $produits = Produit::with([
                'entreprise',
                'categorie',
                'lignesVente',
                'lignesAchat',
                'packages' // charger les packages associés
            ])->get();

            return response()->json(['produits' => $produits], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_id' => 'required|exists:categorie_produits,id',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'code_produit' => 'required|string|max:50|unique:produits,code_produit',
                'prix_unitaire' => 'required|numeric|min:0',
                'unite_mesure' => 'required|string|max:50',
                'packages' => 'nullable|array',
                'packages.*.unite_mesure' => 'required_with:packages|string|max:50',
                'packages.*.prix' => 'required_with:packages|numeric|min:0',
                'packages.*.stock_minimum' => 'sometimes|integer|min:0',
                'packages.*.stock_actuel' => 'sometimes|integer|min:0',
                'packages.*.description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $entrepriseId = auth()->user()->entreprise->id ?? null;
            if (!$entrepriseId) {
                return response()->json(['message' => 'Entreprise introuvable pour utilisateur connecté'], 403);
            }

            $produit = Produit::create(array_merge($request->only([
                'categorie_id',
                'nom',
                'description',
                'code_produit',
                'prix_unitaire',
                'unite_mesure',
                'date_creation',
            ]), [
                'entreprise_id' => $entrepriseId,
            ]));

            // Création des packages si fournis
            if ($request->has('packages')) {
                foreach ($request->packages as $package) {
                    $produit->packages()->create($package);
                }
            }

            $produit->load(['entreprise', 'categorie', 'lignesVente', 'lignesAchat', 'packages']);

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

            $produit = Produit::with([
                'entreprise',
                'categorie',
                'lignesVente',
                'lignesAchat',
                'packages'
            ])->find($id);

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
                'categorie_id' => 'sometimes|required|exists:categorie_produits,id',
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'code_produit' => 'sometimes|required|string|max:50|unique:produits,code_produit,' . $id,
                'prix_unitaire' => 'sometimes|required|numeric|min:0',
                'unite_mesure' => 'sometimes|required|string|max:50',
                'date_creation' => 'sometimes|required|date',
                'packages' => 'nullable|array',
                'packages.*.id' => 'sometimes|exists:produit_packages,id',
                'packages.*.unite_mesure' => 'required_with:packages|string|max:50',
                'packages.*.prix' => 'required_with:packages|numeric|min:0',
                'packages.*.stock_minimum' => 'sometimes|integer|min:0',
                'packages.*.stock_actuel' => 'sometimes|integer|min:0',
                'packages.*.description' => 'nullable|string|max:1000',
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

            $produit->update($request->only([
                'categorie_id',
                'nom',
                'description',
                'code_produit',
                'prix_unitaire',
                'unite_mesure',
                'date_creation'
            ]));

            // Gestion des packages (création et mise à jour)
            if ($request->has('packages')) {
                foreach ($request->packages as $packageData) {
                    if (isset($packageData['id'])) {
                        // mise à jour package existant
                        $produitPackage = $produit->packages()->find($packageData['id']);
                        if ($produitPackage) {
                            $produitPackage->update($packageData);
                        }
                    } else {
                        // création nouveau package
                        $produit->packages()->create($packageData);
                    }
                }
            }

            $produit->load(['entreprise', 'categorie', 'lignesVente', 'lignesAchat', 'packages']);

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
