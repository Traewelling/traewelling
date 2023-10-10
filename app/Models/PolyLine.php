<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PolyLine extends Model
{

    protected $fillable = ['hash', 'polyline', 'source', 'parent_id'];
    protected $casts    = [
        'id'     => 'integer',
        'source' => 'string', //enum['hafas', 'brouter'] in database
    ];

    public function trips(): HasMany {
        return $this->hasMany(HafasTrip::class, 'polyline_id', 'id');
    }

    public function parent(): HasOne {
        return $this->hasOne(PolyLine::class, 'parent_id', 'id');
    }
}
