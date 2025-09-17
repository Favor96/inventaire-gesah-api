<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class MouvementCaisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
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
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function inventaire()
    {
        return $this->belongsTo(InventaireCaisse::class, 'inventaire_caisse_id');
    }

    public function employe()
    {
        return $this->belongsTo(EmployeEntreprise::class, 'employe_id');
    }
}
