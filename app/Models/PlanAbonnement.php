<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanAbonnement extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'label',
        'description',
        'montant',
    ];

    // Relation avec les abonnements
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }
}
