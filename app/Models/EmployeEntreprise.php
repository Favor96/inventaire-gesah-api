<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class EmployeEntreprise extends Model
{
    use HasFactory;

    protected $table = 'employe_entreprises';

    protected $fillable = [
        'entreprise_id',
        'nom',
        'prenom',
        'poste',
        'email',
        'telephone',
        'date_creation',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
    protected $casts = [
        'date_creation' => 'datetime',
    ];

    // Relation avec l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}
