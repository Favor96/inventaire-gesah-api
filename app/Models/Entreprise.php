<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinkla\Hashids\Facades\Hashids;

class Entreprise extends User
{
    use HasFactory;

    protected $table = 'entreprises';

    protected $fillable = [
        'user_id',
        'raison_sociale',
        'secteur_activite',
        'adresse',
        'telephone',
        'email',           // hérité mais on peut le remplir directement
        'date_creation',
        'statut',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Relation avec les abonnements
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation avec les employés
    public function employeEntreprises()
    {
        return $this->hasMany(EmployeEntreprise::class);
    }

}
