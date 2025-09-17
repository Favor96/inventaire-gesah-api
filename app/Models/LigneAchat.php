<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class LigneAchat extends Model
{
    use HasFactory;

    protected $fillable = [
        'achat_id',
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
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function achat()
    {
        return $this->belongsTo(Achat::class, 'achat_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
