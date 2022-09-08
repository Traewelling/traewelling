<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['slug', 'name', 'address_street', 'address_zip', 'address_city', 'latitude', 'longitude'];
    protected $casts    = [
        'name'           => 'string',
        'address_street' => 'string',
        'address_zip'    => 'string',
        'address_city'   => 'string',
        'latitude'       => 'float',
        'longitude'      => 'float',
    ];

    public function checkins(): HasMany {
        return $this->hasMany(LocationCheckin::class, 'location_id', 'id')
                    ->with(['status.locationCheckin'])
                    ->orderByDesc('arrival');
    }
}
