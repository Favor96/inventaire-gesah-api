<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffectationImmo extends Model
{
    use HasFactory;

    protected $fillable = [
        'immobilisation_id',
        'employe_id',
        'date_affectation',
        'statut',
    ];

    protected $casts = [
        'date_affectation' => 'datetime',
    ];

    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class);
    }

    public function employe()
    {
        return $this->belongsTo(EmployeEntreprise::class, 'employe_id');
    }
}
