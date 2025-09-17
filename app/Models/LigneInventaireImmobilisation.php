<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

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
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
    public function inventaire()
    {
        return $this->belongsTo(InventaireImmobilisation::class, 'inventaire_id');
    }

    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class, 'immobilisation_id');
    }
}
