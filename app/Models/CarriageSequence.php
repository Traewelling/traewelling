<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarriageSequence extends Model
{
    use HasFactory;

    protected $fillable = ['stopover_id', 'position', 'sequence', 'vehicle_type', 'vehicle_number', 'order_number'];
    protected $appends  = ['specialType'];

    protected $casts = [
        'stopover_id'    => 'integer',
        'position'       => 'integer',
        'sequence'       => 'string',
        'vehicle_type'   => 'string',
        'vehicle_number' => 'string',
        'order_number'   => 'integer',
    ];

    public function stopover(): BelongsTo {
        return $this->belongsTo(TrainStopover::class, 'stopover_id', 'id');
    }
}
