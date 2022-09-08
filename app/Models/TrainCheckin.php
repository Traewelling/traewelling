<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class TrainCheckin extends Model
{

    use HasFactory;

    protected $fillable = [
        'status_id', 'user_id', 'trip_id', 'origin', 'destination',
        'distance', 'departure', 'arrival', 'points', 'forced',
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['duration', 'origin_stopover', 'destination_stopover', 'speed', 'socialText'];
    protected $casts    = [
        'id'          => 'integer',
        'status_id'   => 'integer',
        'user_id'     => 'integer',
        'origin'      => 'integer',
        'destination' => 'integer',
        'distance'    => 'integer',
        'departure'   => 'datetime',
        'arrival'     => 'datetime',
        'points'      => 'integer',
        'forced'      => 'boolean',
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @deprecated Conflicts with the variable 'origin'. Use ->originStation instead.
     */
    public function Origin(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'origin');
    }

    /**
     * @deprecated Conflicts with the variable 'destination'. Use ->destinationStation instead.
     */
    public function Destination(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'destination');
    }

    public function originStation(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'origin');
    }

    public function destinationStation(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'destination');
    }

    public function HafasTrip(): HasOne {
        return $this->hasOne(HafasTrip::class, 'trip_id', 'trip_id');
    }

    public function getOriginStopoverAttribute(): TrainStopover {
        $stopOver = $this->HafasTrip->stopoversNEW->where('train_station_id', $this->Origin->id)
                                                  ->where('departure_planned', $this->departure)
                                                  ->first();
        if ($stopOver == null) {
            $stopOver = TrainStopover::updateOrCreate(
                [
                    "trip_id"          => $this->trip_id,
                    "train_station_id" => $this->Origin->id
                ],
                [
                    "departure_planned" => $this->departure,
                    "arrival_planned"   => $this->departure,
                ]
            );
            $this->HafasTrip->load('stopoversNEW');
        }
        return $stopOver;
    }

    public function getDestinationStopoverAttribute(): TrainStopover {
        $stopOver = $this->HafasTrip->stopoversNEW->where('train_station_id', $this->Destination->id)
                                                  ->where('arrival_planned', $this->arrival)
                                                  ->first();
        if ($stopOver == null) {
            $stopOver = TrainStopover::updateOrCreate(
                [
                    "trip_id"          => $this->trip_id,
                    "train_station_id" => $this->Destination->id
                ],
                [
                    "departure_planned" => $this->arrival,
                    "arrival_planned"   => $this->arrival,
                ]
            );
            $this->HafasTrip->load('stopoversNEW');
        }
        return $stopOver;
    }

    /**
     * The duration of the journey in minutes
     * @return int
     */
    public function getDurationAttribute(): int {
        return $this->arrival->diffInMinutes($this->departure);
    }

    public function getSpeedAttribute(): float {
        return $this->duration == 0 ? 0 : ($this->distance / 1000) / ($this->duration / 60);
    }

    /**
     * @return Collection
     * @todo Sichtbarkeit der CheckIns prüfen! Hier werden auch Private CheckIns angezeigt
     */
    public function getAlsoOnThisConnectionAttribute(): Collection {
        return self::with(['status'])
                   ->where([
                               ['status_id', '<>', $this->status->id],
                               ['trip_id', '=', $this->HafasTrip->trip_id],
                               ['arrival', '>', $this->departure],
                               ['departure', '<', $this->arrival]
                           ])
                   ->get()
                   ->map(function($trainCheckIn) {
                       return $trainCheckIn->status;
                   })
                   ->filter(function($status) {
                       return $status !== null;
                   });
    }

    public function getSocialTextAttribute(): string {
        $postText = trans_choice(
            key:     'controller.transport.social-post',
            number:  preg_match('/\s/', $this->HafasTrip->linename),
            replace: [
                         'lineName'    => $this->HafasTrip->linename,
                         'destination' => $this->Destination->name
                     ]
        );
        if ($this->status->event !== null) {
            $postText = trans_choice(
                key:     'controller.transport.social-post-with-event',
                number:  preg_match('/\s/', $this->HafasTrip->linename),
                replace: [
                             'lineName'    => $this->HafasTrip->linename,
                             'destination' => $this->Destination->name,
                             'hashtag'     => $this->status->event->hashtag
                         ]
            );
        }


        if (isset($this->status->body)) {
            if ($this->status->event !== null) {
                $eventIntercept = __('controller.transport.social-post-for', [
                    'hashtag' => $this->status->event->hashtag
                ]);
            }

            $appendix = strtr(' (@ :linename ➜ :destination:eventIntercept) #NowTräwelling', [
                ':linename'       => $this->HafasTrip->linename,
                ':destination'    => $this->Destination->name,
                ':eventIntercept' => isset($eventIntercept) ? ' ' . $eventIntercept : ''
            ]);

            $appendixLength = strlen($appendix) + 30;
            $postText       = substr($this->status->body, 0, 280 - $appendixLength);
            if (strlen($postText) !== strlen($this->status->body)) {
                $postText .= '...';
            }
            $postText .= $appendix;
        }

        return $postText;
    }
}
