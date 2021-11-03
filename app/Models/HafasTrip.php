<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HafasTrip extends Model
{

    use HasFactory;

    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'operator_id', 'origin', 'destination',
        'stopovers', 'polyline_id', 'departure', 'arrival', 'delay'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $casts    = [
        'id'          => 'integer',
        'operator_id' => 'integer',
        'origin'      => 'integer',
        'destination' => 'integer',
        'polyline_id' => 'integer',
        'departure'   => 'datetime',
        'arrival'     => 'datetime',
    ];

    public function polyline(): HasOne {
        return $this->hasOne(PolyLine::class, 'id', 'polyline_id');
    }

    public function originStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'origin', 'ibnr');
    }

    public function destinationStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'destination', 'ibnr');
    }

    public function operator(): BelongsTo {
        return $this->belongsTo(HafasOperator::class, 'operator_id', 'id');
    }

    public function stopoversNEW(): HasMany {
        //TODO: Rename to ->stopovers when old attribute is gone
        return $this->hasMany(TrainStopover::class, 'trip_id', 'trip_id')->orderBy('arrival_planned');
    }
}
