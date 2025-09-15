<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementCaisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventaire_caisse_id',
        'client_id',
        'type_mouvement',
        'montant',
        'libelle',
        'reference',
        'date_mouvement',
        'justificatif',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_mouvement' => 'datetime',
    ];

    public function inventaire()
    {
        return $this->belongsTo(InventaireCaisse::class, 'inventaire_caisse_id');
    }

    public function client()
    {
        return $this->belongsTo(ClientEntreprise::class, 'client_id');
    }
}
