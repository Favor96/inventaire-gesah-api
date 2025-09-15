<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneVente extends Model
{

    use HasFactory;

    protected $table = 'ligne_ventes';

    protected $fillable = [
        'vente_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
        'montant_ligne',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'montant_ligne' => 'decimal:2',
    ];

    // Relation vers la vente
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    // Relation vers le produit
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
