<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Achat extends Model
{
    use HasFactory;

    protected $fillable = [
        'fournisseur_id',
        'employe_paye',
        'numero_facture',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'date_achat',
        'date_paiement',
        'mode_paiement',
        'justificatif',
    ];

    protected $casts = [
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'date_achat' => 'datetime',
        'date_paiement' => 'datetime',
    ];

    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function employe()
    {
        return $this->belongsTo(EmployeEntreprise::class, 'employe_paye');
    }

    public function lignes()
    {
        return $this->hasMany(LigneAchat::class, 'achat_id');
    }
}