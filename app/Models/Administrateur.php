<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrateur extends User
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'email',
        'date_creation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
