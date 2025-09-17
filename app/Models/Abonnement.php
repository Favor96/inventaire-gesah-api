<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

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
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Relation vers le plan d'abonnement
    public function plan()
    {
        return $this->belongsTo(PlanAbonnement::class, 'plan_id');
    }
}
