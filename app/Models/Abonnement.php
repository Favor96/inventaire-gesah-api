<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'plan_id',
        'montant_mensuel',
        'date_debut',
        'date_fin',
        'statut',
        'date_paiement',
    ];

    // Relation vers l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Relation vers le plan d'abonnement
    public function plan()
    {
        return $this->belongsTo(PlanAbonnement::class, 'plan_id');
    }
}
