<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'adresse',
        'telephone',
        'email',
        'numero_fiscal',
        'date_creation',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Relation vers l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Relation avec les achats
    public function achats()
    {
        return $this->hasMany(Achat::class);
    }
}
