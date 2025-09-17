<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class PaiementVente extends Model
{
    use HasFactory;

    protected $fillable = [
        'vente_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference',
        'justificatif',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
    protected $casts = [
        'date_paiement' => 'datetime',
        'montant' => 'decimal:2',
    ];

    // Relation vers la vente
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }
}
