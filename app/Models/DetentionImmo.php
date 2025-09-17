<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

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
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class);
    }

    public function employe()
    {
        return $this->belongsTo(EmployeEntreprise::class, 'employe_id');
    }
}
