<?php

namespace App\Models;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids as FacadesHashids;

class Administrateur extends User
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'email',
    ];
    protected $appends = ['hashid'];
    protected $hidden = ['id','user_id'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getHashidAttribute()
    {
        return FacadesHashids::encode($this->id);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
