<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type'];
    protected $casts    = [
        'name' => 'string',
        'type' => 'string', //maybe useful for future implementations -> enum('train', 'airplane', 'other')
    ];

    public function vehicles(): HasMany {
        return $this->hasMany(Vehicle::class, 'vehicle_group_id', 'id');
    }
}
