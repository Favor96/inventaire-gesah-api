<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class InventaireCaisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'agent_id',
        'caisse_id',
        'numero_inventaire',
        'solde_theorique',
        'solde_reel',
        'ecart',
        'date_inventaire',
        'observations',
        'statut',
        'date_fin',
        'date_debut'
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    protected $casts = [
        'solde_theorique' => 'decimal:2',
        'solde_reel' => 'decimal:2',
        'ecart' => 'decimal:2',
        'date_inventaire' => 'datetime',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function caisse()
    {
        return $this->belongsTo(Caisse::class);
    }
}
