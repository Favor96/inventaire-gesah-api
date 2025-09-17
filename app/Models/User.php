<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'is_verified',
        'role',
        'verification_code'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class, 'user_id');
    }

    // Relation avec le chef de mission (si ce user est un chef)
    public function chefMission()
    {
        return $this->hasOne(ChefMission::class, 'user_id');
    }

    // Relation avec l'agent (si ce user est un agent)
    public function agent()
    {
        return $this->hasOne(Agent::class, 'user_id');
    }
    public function entreprise()
    {
        return $this->hasOne(Entreprise::class, 'user_id');
    }

}
