<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class ClientEntreprise extends Model
{
    use HasFactory;

    protected $table = 'client_entreprises';

    protected $fillable = [
        'entreprise_id',
        'nom',
        'prenom',
        'email',
        'telephone',
    ];
    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
    // Relation avec l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}
