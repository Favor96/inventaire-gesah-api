<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class CategorieProduit extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'description',
    ];

    // Relation avec l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
    // Relation avec les produits
    public function produits()
    {
        return $this->hasMany(Produit::class, 'categorie_id');
    }
}
