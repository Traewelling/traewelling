<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'vehicle_group_id', 'classification'];
    protected $casts    = [
        'name'             => 'string',
        'vehicle_group_id' => 'integer',
        'classification'   => 'string',
    ];

    public function vehicleGroup(): BelongsTo {
        return $this->belongsTo(VehicleGroup::class, 'vehicle_group_id', 'id');
    }
}
