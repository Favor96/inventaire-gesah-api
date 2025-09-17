<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Immobilisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'type_id',
        'nom',
        'description',
        'numero_serie',
        'valeur_acquisition',
        'date_acquisition',
        'date_mise_service',
        'etat',
        'localisation',
    ];

    protected $casts = [
        'valeur_acquisition' => 'decimal:2',
        'date_acquisition' => 'datetime',
        'date_mise_service' => 'datetime',
    ];

    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function type()
    {
        return $this->belongsTo(TypeImmobilisation::class, 'type_id');
    }
}
