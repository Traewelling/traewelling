<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolyLine extends Model
{

    protected $fillable = ['hash', 'polyline', 'source'];
    protected $casts    = [
        'id'     => 'integer',
        'source' => 'string', //enum['hafas', 'brouter'] in database
    ];

    public function trips(): HasMany {
        return $this->hasMany(HafasTrip::class, 'polyline_id', 'id');
    }
}
