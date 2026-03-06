<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\HafasTravelType;
use App\Exceptions\ManualTripValidationException;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Requests\ManualTripCreationRequest;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TripResource;
use App\Models\Checkin;
use App\Models\Operator;
use App\Models\Station;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;
use Throwable;

class TripController extends Controller
{
    #[OA\Get(
        path: '/trips/{id}/statuses',
        operationId: 'getTripStatuses',
        description: 'Returns all statuses visible to the (un)authenticated user for a given trip',
        summary: '[Auth optional] Get statuses for a trip',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Trips'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Internal trip ID (available as checkin.trip in the status response)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 4711,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Trip not found'),
        ],
    )]
    public function statuses(int $id): AnonymousResourceCollection|JsonResponse
    {
        $trip = Trip::find($id);

        if ($trip === null) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        $statuses = Checkin::with(['status.user'])
            ->where('trip_id', $trip->trip_id)
            ->get()
            ->map(fn (Checkin $checkin) => $checkin->status)
            ->filter(fn ($status) => $status !== null && Gate::allows('view', $status))
            ->values();

        return StatusResource::collection($statuses);
    }

    /**
     * @todo add docs when endpoint is stable
     */
    public function createTrip(ManualTripCreationRequest $request): TripResource|JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $creator = new ManualTripCreator();
            $creator->setCategory(HafasTravelType::from($validated['category']))
                ->setLine($validated['lineName'], $validated['journeyNumber'])
                ->setOrigin(
                    Station::findOrFail($validated['originId']),
                    Carbon::parse($validated['originDeparturePlanned']),
                    isset($validated['originDepartureReal']) ? Carbon::parse($validated['originDepartureReal']) : null
                )
                ->setDestination(
                    Station::findOrFail($validated['destinationId']),
                    Carbon::parse($validated['destinationArrivalPlanned']),
                    isset($validated['destinationArrivalReal']) ? Carbon::parse($validated['destinationArrivalReal']) : null
                );

            if (isset($validated['operatorId'])) {
                $operator = Operator::findOrFail($validated['operatorId']);
                $creator->setOperator($operator);
            }

            foreach ($validated['stopovers'] ?? [] as $stopover) {
                $creator->addStopover(
                    Station::findOrFail($stopover['stationId']),
                    isset($stopover['departure']) ? Carbon::parse($stopover['departure']) : null,
                    isset($stopover['arrival']) ? Carbon::parse($stopover['arrival']) : null,
                    isset($stopover['departureReal']) ? Carbon::parse($stopover['departureReal']) : null,
                    isset($stopover['arrivalReal']) ? Carbon::parse($stopover['arrivalReal']) : null
                );
            }

            $trip = $creator->createFullTrip();
            $durationInHours = $trip->departure->diffInHours($trip->arrival);
            if ($durationInHours > config('trwl.max_journey_hours')) {
                throw new ManualTripValidationException(sprintf('Trip duration exceeds maximum allowed duration of %d hours', config('trwl.max_journey_hours')));
            }

        } catch (ManualTripValidationException $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json(['message' => 'An error occurred while creating the trip'], 500);
        }

        DB::commit();

        return new TripResource($trip);
    }
}
