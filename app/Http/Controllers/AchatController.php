<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

class AchatController extends Controller
{
    public function index()
    {
        try {
            $achats = Achat::with(['inventaire', 'fournisseur', 'employe', 'lignes'])->get();
            return response()->json(['achats' => $achats], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fournisseur_id' => 'required|exists:fournisseurs,id',
                'employe_paye' => 'required|exists:employe_entreprises,id',
                'numero_facture' => 'required|string|max:100',
                'montant_ht' => 'required|numeric|min:0',
                'montant_tva' => 'required|numeric|min:0',
                'montant_ttc' => 'required|numeric|min:0',
                'date_achat' => 'required|date',
                'date_paiement' => 'required|date',
                'mode_paiement' => 'required|string|max:50',
                'justificatif' => 'nullable|image|max:2048',

                // Validation des lignes d’achat
                'lignes' => 'required|array|min:1',
                'lignes.*.produit_id' => 'required|exists:produits,id',
                'lignes.*.quantite' => 'required|integer|min:1',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'lignes.*.montant_ligne' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only([
                'fournisseur_id',
                'employe_paye',
                'numero_facture',
                'montant_ht',
                'montant_tva',
                'montant_ttc',
                'date_achat',
                'date_paiement',
                'mode_paiement'
            ]);

            // Gestion du justificatif
            if ($request->hasFile('justificatif')) {
                $path = $request->file('justificatif')->store('justificatifs', 'public');
                $data['justificatif'] = $path;
            }

            // Créer l’achat
            $achat = Achat::create($data);

            // Créer les lignes associées
            foreach ($request->lignes as $ligne) {
                $achat->lignes()->create($ligne);
            }

            // Charger toutes les relations
            $achat = Achat::with(['inventaire', 'fournisseur', 'employe', 'lignes.produit'])->find($achat->id);

            return response()->json(['message' => 'Achat créé avec succès', 'achat' => $achat], 201);
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

            $achat = Achat::with(['inventaire', 'fournisseur', 'employe', 'lignes'])->find($id);
            if (!$achat) {
                return response()->json(['message' => 'Achat non trouvé'], 404);
            }
            return response()->json(['achat' => $achat], 200);
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
                'fournisseur_id' => 'sometimes|required|exists:fournisseurs,id',
                'employe_paye' => 'sometimes|required|exists:employe_entreprises,id',
                'numero_facture' => 'sometimes|required|string|max:100',
                'montant_ht' => 'sometimes|required|numeric|min:0',
                'montant_tva' => 'sometimes|required|numeric|min:0',
                'montant_ttc' => 'sometimes|required|numeric|min:0',
                'date_achat' => 'sometimes|required|date',
                'date_paiement' => 'sometimes|required|date',
                'mode_paiement' => 'sometimes|required|string|max:50',
                'justificatif' => 'nullable|image|max:2048',

                // Validation des lignes d’achat
                'lignes' => 'nullable|array',
                'lignes.*.hashid' => 'nullable|string',
                'lignes.*.produit_id' => 'required|exists:produits,id',
                'lignes.*.quantite' => 'required|integer|min:1',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'lignes.*.montant_ligne' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $achat = Achat::find($id);
            if (!$achat) {
                return response()->json(['message' => 'Achat non trouvé'], 404);
            }

            $data = $request->only([
                'fournisseur_id',
                'employe_paye',
                'numero_facture',
                'montant_ht',
                'montant_tva',
                'montant_ttc',
                'date_achat',
                'date_paiement',
                'mode_paiement'
            ]);

            // Gestion de l'image justificatif
            if ($request->hasFile('justificatif')) {
                if ($achat->justificatif) {
                    Storage::disk('public')->delete($achat->justificatif);
                }
                $path = $request->file('justificatif')->store('justificatifs', 'public');
                $data['justificatif'] = $path;
            }

            // Update des infos principales
            $achat->update($data);

            // Gestion des lignes d'achat
            if ($request->has('lignes')) {
                $ligneIds = [];
                foreach ($request->lignes as $ligne) {
                    if (!empty($ligne['hashid'])) {
                        // Mise à jour
                        $decoded = Hashids::decode($ligne['hashid']);
                        if (!empty($decoded)) {
                            $ligneId = $decoded[0];
                            $ligneAchat = \App\Models\LigneAchat::where('achat_id', $achat->id)->find($ligneId);
                            if ($ligneAchat) {
                                $ligneAchat->update([
                                    'produit_id' => $ligne['produit_id'],
                                    'quantite' => $ligne['quantite'],
                                    'prix_unitaire' => $ligne['prix_unitaire'],
                                    'montant_ligne' => $ligne['montant_ligne'],
                                ]);
                                $ligneIds[] = $ligneAchat->id;
                            }
                        }
                    } else {
                        // Création
                        $newLigne = \App\Models\LigneAchat::create([
                            'achat_id' => $achat->id,
                            'produit_id' => $ligne['produit_id'],
                            'quantite' => $ligne['quantite'],
                            'prix_unitaire' => $ligne['prix_unitaire'],
                            'montant_ligne' => $ligne['montant_ligne'],
                        ]);
                        $ligneIds[] = $newLigne->id;
                    }
                }

                // Suppression des lignes non envoyées (optionnel)
                \App\Models\LigneAchat::where('achat_id', $achat->id)
                    ->whereNotIn('id', $ligneIds)
                    ->delete();
            }

            $achat = Achat::with(['inventaire', 'fournisseur', 'employe', 'lignes'])->find($achat->id);

            return response()->json(['message' => 'Achat mis à jour', 'achat' => $achat], 200);
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

            $achat = Achat::with('lignes')->find($id);
            if (!$achat) {
                return response()->json(['message' => 'Achat non trouvé'], 404);
            }

            // Supprimer le justificatif si existe
            if ($achat->justificatif) {
                Storage::disk('public')->delete($achat->justificatif);
            }

            // Supprimer toutes les lignes associées
            foreach ($achat->lignes as $ligne) {
                $ligne->delete();
            }

            // Supprimer l'achat
            $achat->delete();

            return response()->json(['message' => 'Achat supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}