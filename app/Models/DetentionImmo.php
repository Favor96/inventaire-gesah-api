<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetentionImmo extends Model
{
    use HasFactory;

    protected $fillable = [
        'immobilisation_id',
        'employe_id',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class);
    }

    public function employe()
    {
        return $this->belongsTo(EmployeEntreprise::class, 'client_id');
    }
}
