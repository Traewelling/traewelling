<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainStation extends Model
{

    use HasFactory;

    protected $fillable = ['ibnr', 'rilIdentifier', 'name', 'latitude', 'longitude'];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $casts    = [
        'latitude'  => 'double',
        'longitude' => 'double'
    ];

    public function events(): HasMany {
        return $this->hasMany(Event::class, 'trainstation', 'id');
    }
}
