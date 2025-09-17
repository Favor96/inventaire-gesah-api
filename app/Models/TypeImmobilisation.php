<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class TypeImmobilisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
    ];

    protected $appends = ['hashid'];
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Relation avec les immobilisations
    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'type_id');
    }
}
