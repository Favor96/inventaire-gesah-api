<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'categorie_id',
        'nom',
        'description',
        'code_produit',
        'prix_unitaire',
        'unite_mesure',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
    ];

    protected $appends = ['hashid'];

    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Relations

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieProduit::class, 'categorie_id');
    }

    public function lignesVente()
    {
        return $this->hasMany(LigneVente::class);
    }

    public function lignesAchat()
    {
        return $this->hasMany(LigneAchat::class);
    }

    public function packages()
    {
        return $this->hasMany(ProduitPackage::class);
    }
}
