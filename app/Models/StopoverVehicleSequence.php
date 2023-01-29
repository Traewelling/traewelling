<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StopoverVehicleSequence extends Model
{
    use HasFactory;

    protected $fillable = ['stopover_id', 'position', 'sequence', 'vehicle_id', 'order_number'];

    protected $casts = [
        'stopover_id'  => 'integer',
        'position'     => 'integer',
        'sequence'     => 'string',
        'vehicle_id'   => 'integer',
        'order_number' => 'integer',
    ];

    public function stopover(): BelongsTo {
        return $this->belongsTo(TrainStopover::class, 'stopover_id', 'id');
    }

    public function vehicle(): BelongsTo {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }
}
