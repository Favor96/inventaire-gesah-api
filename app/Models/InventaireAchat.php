<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventaireAchat extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'agent_id',
        'numero_inventaire',
        'date_inventaire',
        'periode_debut',
        'periode_fin',
        'statut',
        'date_validation',
    ];

    protected $casts = [
        'date_inventaire' => 'datetime',
        'periode_debut' => 'datetime',
        'periode_fin' => 'datetime',
        'date_validation' => 'datetime',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function achats()
    {
        return $this->hasMany(Achat::class, 'inventaire_achat_id');
    }
}
