<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolyLine extends Model
{

    protected $fillable = ['hash', 'polyline'];
    protected $casts    = [
        'id' => 'integer',
    ];

    public function trips(): HasMany {
        return $this->hasMany(HafasTrip::class, 'polyline_id', 'id');
    }
}
