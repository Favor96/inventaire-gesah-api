<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
