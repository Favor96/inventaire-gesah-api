<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'stock_minimum',
        'stock_actuel',
        'unite_mesure',
        'date_creation',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'stock_minimum' => 'integer',
        'stock_actuel' => 'integer',
        'date_creation' => 'datetime',
    ];

    // Relation avec l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Relation avec la catégorie
    public function categorie()
    {
        return $this->belongsTo(CategorieProduit::class, 'categorie_id');
    }

    // Relation avec les lignes de vente
    public function lignesVente()
    {
        return $this->hasMany(LigneVente::class);
    }

    // Relation avec les lignes d'achat
    public function lignesAchat()
    {
        return $this->hasMany(LigneAchat::class);
    }
}
