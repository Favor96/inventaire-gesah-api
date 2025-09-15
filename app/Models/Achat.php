<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventaire_achat_id',
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

    public function inventaire()
    {
        return $this->belongsTo(InventaireAchat::class, 'inventaire_achat_id');
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
