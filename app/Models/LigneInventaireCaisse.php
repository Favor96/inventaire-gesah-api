<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneInventaireCaisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventaire_id',
        'type_billet',
        'nombre',
        'montant',
    ];

    public function inventaire()
    {
        return $this->belongsTo(InventaireCaisse::class, 'inventaire_id');
    }
}
