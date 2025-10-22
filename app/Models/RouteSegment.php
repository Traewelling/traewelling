<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Traewelling\GooglePolyline\dto\Location;
use Traewelling\GooglePolyline\PolylineTranscoder;

class RouteSegment extends Model
{
    use HasUuids;

    protected $fillable = [
        'from_station',
        'to_station',
        'distance',
        'duration',
        'polyline',
        'polyline_precision',
        'path_type',
    ];

    public function fromStation(): BelongsTo {
        return $this->belongsTo(Station::class, 'from_station_id');
    }

    public function toStation(): BelongsTo {
        return $this->belongsTo(Station::class, 'to_station_id');
    }

    /**
     * @return Location[]
     */
    public function getCoordinates(): array {
        $precision = $this->polyline_precision ?? 5;
        return (new PolylineTranscoder)->decodePolyline($this->polyline, $precision);
    }
}
