<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    // Relation avec les abonnements
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    // Relation avec les employés
    public function employeEntreprises()
    {
        return $this->hasMany(EmployeEntreprise::class);
    }

    // Scope global pour filtrer uniquement les entreprises
    protected static function booted()
    {
        static::addGlobalScope('role', function ($query) {
            $query->where('role', 'client');
        });
    }
}
