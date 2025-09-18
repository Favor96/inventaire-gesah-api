<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Vinkla\Hashids\Facades\Hashids;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'email',
        'password',
        'is_verified',
        'role',
        'verification_code'
    ];

    protected $hidden = [
        'password',
        'id'
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class, 'user_id');
    }

    // Relation avec le chef de mission (si ce user est un chef)
    public function chef()
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
