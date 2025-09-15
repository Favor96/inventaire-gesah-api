<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneInventaireStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventaire_id',
        'produit_id',
        'quantite_theorique',
        'quantite_reelle',
        'ecart',
        'valeur_ecart',
        'observations',
    ];

    protected $casts = [
        'quantite_theorique' => 'integer',
        'quantite_reelle' => 'integer',
        'ecart' => 'integer',
        'valeur_ecart' => 'decimal:2',
    ];

    public function inventaire()
    {
        return $this->belongsTo(InventaireStock::class, 'inventaire_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
