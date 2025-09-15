<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'date_creation' => 'datetime',
    ];

    // Relation avec l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}
