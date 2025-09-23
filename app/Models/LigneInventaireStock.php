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
        'produit_package_id',
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

    public function produitPackage()
    {
        return $this->belongsTo(ProduitPackage::class, 'produit_package_id');
    }


    protected static function booted()
    {
        static::saving(function ($ligne) {
            if ($ligne->produitPackage) {
                $quantiteTheorique = $ligne->produitPackage->stock_actuel;

                if (!is_null($ligne->quantite_reelle)) {
                    $ligne->ecart = $ligne->quantite_reelle - $quantiteTheorique;
                }

                if (!is_null($ligne->ecart)) {
                    $ligne->valeur_ecart = $ligne->ecart * $ligne->produitPackage->prix;
                }

                $ligne->quantite_theorique = $quantiteTheorique;
            }
        });
    }
}
