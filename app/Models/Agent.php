<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinkla\Hashids\Facades\Hashids;

class Agent extends User
{
    use HasFactory;

    protected $table = 'agents';

    protected $fillable = [
        'user_id',
        'chef_id',      // chef qui gère l'agent
        'nom',
        'prenom',
        'email',
        'telephone',
        'specialite',
        'actif',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Scope global pour filtrer automatiquement par role
    protected static function booted()
    {
        static::addGlobalScope('role', function ($query) {
            $query->where('role', 'agent');
        });
    }

    // Relation avec le chef de mission
    public function chef()
    {
        return $this->belongsTo(ChefMission::class, 'chef_id');
    }
}
