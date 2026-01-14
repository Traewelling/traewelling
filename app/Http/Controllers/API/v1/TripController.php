<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Enum\HafasTravelType;
use App\Exceptions\ManualTripValidationException;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Requests\ManualTripCreationRequest;
use App\Http\Resources\TripResource;
use App\Models\Operator;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class TripController extends Controller
{
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
