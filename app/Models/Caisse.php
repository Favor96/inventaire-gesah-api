<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'nom_caisse',
        'type_caisse',
        'solde_initial',
        'solde_actuel',
        'date_creation',
    ];

    protected $casts = [
        'solde_initial' => 'decimal:2',
        'solde_actuel' => 'decimal:2',
        'date_creation' => 'datetime',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function inventaires()
    {
        return $this->hasMany(InventaireCaisse::class, 'caisse_id');
    }
}
