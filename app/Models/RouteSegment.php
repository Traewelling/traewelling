<?php

namespace App\Models;

use App\Dto\Coordinate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function stopOvers(): BelongsToMany {
        return $this->belongsToMany(Stopover::class, 'train_stopovers', 'route_segment_id', 'id');
    }

    /**
     * @return Coordinate[]
     */
    public function getCoordinates(): array {
        $precision = $this->polyline_precision ?? 5;
        $locations = (new PolylineTranscoder)->decodePolyline($this->polyline, $precision);

        $coordinates = [];
        foreach ($locations as $key => $location) {
            $coordinates[] = new Coordinate($location->getLatitude(), $location->getLongitude());
            unset($locations[$key]);
        }

        return $coordinates;
    }
}
