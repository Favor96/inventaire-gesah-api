<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

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
    protected $appends = ['hashid'];
    protected $hidden = ['user_id','administrateur_id'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function administrateur()
    {
        return $this->belongsTo(Administrateur::class, 'administrateur_id');
    }
    // Relation avec les agents gérés par ce chef
    public function agents()
    {
        return $this->hasMany(Agent::class, 'chef_id');
    }

}
