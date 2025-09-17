<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Vente extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'date_vente',
        'total',
        'statut',
    ];

    protected $casts = [
        'date_vente' => 'datetime',
        'total' => 'decimal:2',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Relation vers le client
    public function client()
    {
        return $this->belongsTo(ClientEntreprise::class, 'client_id');
    }

  

    // Relation avec les lignes de vente
    public function lignesVente()
    {
        return $this->hasMany(LigneVente::class);
    }
}
