<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'produit_id',
        'unite_mesure',
        'prix',
        'stock_minimum',
        'stock_actuel',
        'description',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'stock_minimum' => 'integer',
        'stock_actuel' => 'integer',
    ];


    // Relation inverse vers produit
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
