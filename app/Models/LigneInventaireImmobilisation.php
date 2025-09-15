<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneInventaireImmobilisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventaire_id',
        'immobilisation_id',
        'etat_constate',
        'localisation_constatee',
        'valeur_estimee',
        'observations',
        'present',
    ];

    protected $casts = [
        'valeur_estimee' => 'decimal:2',
        'present' => 'boolean',
    ];

    public function inventaire()
    {
        return $this->belongsTo(InventaireImmobilisation::class, 'inventaire_id');
    }

    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class, 'immobilisation_id');
    }
}
