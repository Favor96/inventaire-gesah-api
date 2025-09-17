<?php

namespace App\Http\Controllers;

use App\Models\PaiementVente;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PaiementVenteController extends Controller
{
    public function index()
    {
        try {
            $paiements = PaiementVente::with('vente')->get();
            return response()->json(['paiements' => $paiements], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vente_id' => 'required|exists:ventes,id',
                'date_paiement' => 'required|date',
                'montant' => 'required|numeric|min:0',
                'mode_paiement' => 'required|string|max:50',
                'reference' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $vente = Vente::with('lignesVente')->find($request->vente_id);
            if (!$vente) {
                return response()->json(['message' => 'Vente non trouvée'], 404);
            }

            // Création du paiement
            $paiement = PaiementVente::create([
                'vente_id' => $vente->id,
                'date_paiement' => $request->date_paiement,
                'montant' => $request->montant,
                'mode_paiement' => $request->mode_paiement,
                'reference' => $request->reference,
            ]);

            // Vérifier si le paiement couvre la vente pour changer le statut
            $total_paye = $vente->paiements()->sum('montant');
            if ($total_paye >= $vente->total && $vente->statut !== 'payee') {
                $vente->statut = 'payee';
                $vente->save();

                // Retirer le stock si pas encore fait
                foreach ($vente->lignesVente as $ligne) {
                    $produit = $ligne->produit;
                    if ($produit) {
                        $produit->stock_actuel -= $ligne->quantite;
                        if ($produit->stock_actuel < 0) $produit->stock_actuel = 0;
                        $produit->save();
                    }
                }
            }

            // Génération du PDF du reçu
            $filename = 'recu_paiement_' . $paiement->id . '.pdf';
            $path = 'paiements/' . $filename;

            $pdf = Pdf::loadView('recu', [
                'vente' => $vente,
                'paiement' => $paiement
            ]);

            // Sauvegarde du PDF
            Storage::disk('public')->put($path, $pdf->output());

            // Enregistrement du chemin dans le champ justificatif
            $paiement->justificatif = $path;
            $paiement->save();

            // Retourne le PDF à l’application mobile
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $paiement = PaiementVente::with('vente')->find($ids[0]);
            if (!$paiement) return response()->json(['message' => 'Paiement non trouvé'], 404);

            return response()->json(['paiement' => $paiement], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $paiement = PaiementVente::find($ids[0]);
            if (!$paiement) return response()->json(['message' => 'Paiement non trouvé'], 404);

            $validator = Validator::make($request->all(), [
                'date_paiement' => 'sometimes|date',
                'montant' => 'sometimes|numeric|min:0',
                'mode_paiement' => 'sometimes|string|max:50',
                'reference' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }

            $paiement->update($request->only(['date_paiement', 'montant', 'mode_paiement', 'reference']));

            return response()->json(['message' => 'Paiement mis à jour', 'paiement' => $paiement], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) return response()->json(['message' => 'ID invalide'], 400);

            $paiement = PaiementVente::find($ids[0]);
            if (!$paiement) return response()->json(['message' => 'Paiement non trouvé'], 404);

            // Supprimer le PDF du stockage si existant
            if ($paiement->justificatif && Storage::disk('public')->exists($paiement->justificatif)) {
                Storage::disk('public')->delete($paiement->justificatif);
            }

            $paiement->delete();
            return response()->json(['message' => 'Paiement supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
