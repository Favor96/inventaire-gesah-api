<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class PlanAbonnement extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'label',
        'description',
        'montant',
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
}
