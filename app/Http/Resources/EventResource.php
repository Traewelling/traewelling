<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Event",
 *     @OA\Property(property="id", type="integer", example=39),
 *     @OA\Property(property="name", type="string", example="9-Euro-Ticket"),
 *     @OA\Property(property="slug", type="string", example="9_euro_ticket"),
 *     @OA\Property(property="hashtag", type="string", example="NeunEuroTicket"),
 *     @OA\Property(property="host", type="string", example="9-Euro-Ticket GmbH"),
 *     @OA\Property(property="url", type="string", example="https://9-euro-ticket.de"),
 *     @OA\Property(property="begin", type="string", format="date-time", example="2022-01-01T00:00:00+00:00"),
 *     @OA\Property(property="end", type="string", format="date-time", example="2022-01-02T00:00:00+00:00"),
 *     @OA\Property(property="station", type="string", ref="#/components/schemas/Station")
 * )
 */
class EventResource extends JsonResource
{

    public function toArray($request): array {
        return [
            "id"      => $this->id,
            "name"    => $this->name,
            "slug"    => $this->slug,
            "hashtag" => $this->hashtag,
            "host"    => $this->host,
            "url"     => $this->url,
            "begin"   => ($this->event_start ?? $this->checkin_start)->toIso8601String(),
            "end"     => ($this->event_end ?? $this->checkin_end)->toIso8601String(),
            "station" => new StationResource($this->station)
        ];
    }
}
