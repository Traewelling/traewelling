<?php

namespace App\Models;

use App\StationIdentifierType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationIdentifier extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['relevance', 'station_id', 'identifier', 'type', 'origin', 'name'];
    protected $visible  = [
        'station_id',
        'identifier',
        'type',
        'origin',
        // Relations
        'station',
        'relevance',
    ];
    protected $casts = [
        'type' => StationIdentifierType::class,
    ];

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class);
    }

    public function getRawTransitousApiLinkToDepartures(): string {
        $params = [
            'stopId' => $this->identifier,
            'radius' => config('trwl.motis.radius'),
            'n'      => config('trwl.motis.results'),
        ];
        return 'https://api.transitous.org/api/v1/stoptimes?' . http_build_query($params);
    }
}
