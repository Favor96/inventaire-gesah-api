<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class InventaireImmobilisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'agent_id',
        'numero_inventaire',
        'date_inventaire',
        'statut',
        'observations',
        'date_validation',
    ];

    protected $casts = [
        'date_inventaire' => 'datetime',
        'date_validation' => 'datetime',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneInventaireImmobilisation::class, 'inventaire_id');
    }
}
