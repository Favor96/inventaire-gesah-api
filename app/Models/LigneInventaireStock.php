<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

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
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
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

    protected static function booted()
    {
        static::saving(function ($ligne) {
            if ($ligne->produit) {
                // quantité théorique = stock actuel du produit
                $quantiteTheorique = $ligne->produit->stock_actuel;

                // calcul de l’écart
                if (!is_null($ligne->quantite_reelle)) {
                    $ligne->ecart = $ligne->quantite_reelle - $quantiteTheorique;
                }

                // calcul de la valeur de l’écart
                if (!is_null($ligne->ecart)) {
                    $ligne->valeur_ecart = $ligne->ecart * $ligne->produit->prix_unitaire;
                }
            }
        });
    }
}
