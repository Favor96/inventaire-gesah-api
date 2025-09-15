<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChefMission extends User
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'administrateur_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'poste',
        'actif',
        'date_embauche',
    ];

    // Scope global pour filtrer automatiquement par role
    protected static function booted()
    {
        static::addGlobalScope('role', function ($query) {
            $query->where('role', 'chef_de_mission');
        });
    }

    // Relation avec l'admin qui gère le chef
    public function administrateur()
    {
        return $this->belongsTo(Administrateur::class);
    }

    // Relation avec les agents gérés par ce chef
    public function agents()
    {
        return $this->hasMany(Agent::class, 'chef_id');
    }
}
