<?php

namespace App\Http\Requests;

use App\Enum\Business;
use App\Enum\StationIdentifierType;
use App\Enum\StatusVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CheckinRequestBody',
    title: 'CheckinRequestBody',
    description: 'Fields for creating a transit checkin',
    properties: [
        new OA\Property(property: 'body', type: 'string', example: 'Meine erste Fahrt nach Knuffingen!', nullable: true, maxLength: 280),
        new OA\Property(property: 'business', ref: Business::class),
        new OA\Property(property: 'visibility', ref: StatusVisibility::class),
        new OA\Property(property: 'eventId', description: 'Id of an event the status should be connected to', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'toot', description: 'Should this status be posted to mastodon?', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'chainPost', description: 'Should this status be posted to mastodon as a chained post?', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'ibnr', description: 'If true, `start` and `destination` can be supplied as IBNR. Otherwise Träwelling-ID. Default: false.', type: 'boolean', example: true, nullable: true, deprecated: true),
        new OA\Property(property: 'tripId', description: 'The tripId for the trip to check into', type: 'string', example: 'b37ff515-22e1-463c-94de-3ad7964b5cb8', nullable: true),
        new OA\Property(property: 'lineName', description: 'The line name for the trip to check into', type: 'string', example: 'S 4', nullable: true),
        new OA\Property(property: 'start', description: 'Träwelling-Station-ID of the starting point, required without startIdentifier', type: 'integer', example: 8000191),
        new OA\Property(property: 'startIdentifier', description: '(EXPERIMENTAL: this is not guaranteed to work. It might lead to inconsistent behaviour) External station identifier of the starting point, required without startIdentifier, requires startIdentifierType', type: 'string', example: 'de-0815-1234:56:78'),
        new OA\Property(property: 'startIdentifierType', ref: StationIdentifierType::class),
        new OA\Property(property: 'destination', description: 'Träwelling-Station-ID of the destination, required without destinationIdentifier', type: 'integer', example: 8000192),
        new OA\Property(property: 'destinationIdentifier', description: '(EXPERIMENTAL: this is not guaranteed to work. It might lead to inconsistent behaviour) External station identifier of the destination, required without destinationIdentifier, requires destinationIdentifierType', type: 'string', example: 'de-0815-1234:56:78'),
        new OA\Property(property: 'destinationIdentifierType', ref: StationIdentifierType::class),
        new OA\Property(property: 'departure', description: 'Timestamp of the departure', type: 'string', format: 'date-time', example: '2022-12-19T20:41:00+01:00'),
        new OA\Property(property: 'arrival', description: 'Timestamp of the arrival', type: 'string', format: 'date-time', example: '2022-12-19T20:42:00+01:00'),
        new OA\Property(property: 'force', description: 'If true, the checkin is created even on collision. No points awarded.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'with', description: 'Also check in these user IDs (max. 10). Requires mutual follow.', type: 'array', items: new OA\Items(type: 'integer', example: 1), nullable: true),
    ],
)]
/**
 * @property string $body
 * @property string|null $business
 * @property string|null $visibility
 * @property int|null $eventId
 * @property bool|null $toot
 * @property bool|null $chainPost
 * @property bool|null $ibnr
 * @property string|null $tripId
 * @property string|null $lineName
 * @property int|null $start
 * @property string|null $startIdentifier
 * @property string|null $startIdentifierType
 * @property int|null $destination
 * @property string|null $destinationIdentifier
 * @property string|null $destinationIdentifierType
 * @property string $departure
 * @property string $arrival
 * @property bool|null $force
 * @property int[]|null $with
 */
class CheckinRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['nullable', 'max:280'],
            'business' => ['nullable', new Enum(Business::class)],
            'visibility' => ['nullable', new Enum(StatusVisibility::class)],
            'eventId' => ['nullable', 'integer', 'exists:events,id'],
            'toot' => ['nullable', 'boolean'],
            'chainPost' => ['nullable', 'boolean'],
            'ibnr' => ['nullable', 'boolean'],
            'tripId' => ['required'],
            'lineName' => ['required'],
            'start' => ['required_without:startIdentifier', 'numeric'],
            'startIdentifier' => ['required_without:start'],
            'startIdentifierType' => ['nullable', 'required_with:startIdentifier', new Enum(StationIdentifierType::class)],
            'destination' => ['required_without:destinationIdentifier', 'numeric'],
            'destinationIdentifier' => ['required_without:destination'],
            'destinationIdentifierType' => ['nullable', 'required_with:destinationIdentifier', new Enum(StationIdentifierType::class)],
            'departure' => ['required', 'date'],
            'arrival' => ['required', 'date'],
            'force' => ['nullable', 'boolean'],
            'with' => ['nullable', 'array', 'max:10'],
        ];
    }
}
