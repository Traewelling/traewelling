<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\HafasTravelType;
use App\Enum\PointReason;
use App\Http\Controllers\Controller;
use App\Http\Resources\PointsCalculationResource;
use App\Models\CarriageSequence;
use App\Models\HafasTrip;
use App\Models\TrainStopover;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\Pure;
use K118\DB\Exceptions\TrainNotFoundException;
use K118\DB\Wagenreihung;
use Throwable;

abstract class CarriageSequenceController extends Controller
{
    /**
     * It's not recommended to call this method directly.
     * Consider using the background job using `FetchCarriageSequence::dispatch($stopover)`
     *
     * @param TrainStopover $stopover
     * @param bool          $force
     *
     * @return void
     */
    public static function fetchSequence(TrainStopover $stopover, bool $force = false): void {
        if (
            $stopover->trip->category !== HafasTravelType::NATIONAL
            && $stopover->trip->category !== HafasTravelType::NATIONAL_EXPRESS
            && $stopover->trip->category !== HafasTravelType::REGIONAL
            && $stopover->trip->category !== HafasTravelType::REGIONAL_EXP
        ) {
            //Type not supported
            return;
        }
        if (!$force && $stopover->carriageSequences->count() > 0) {
            //Already fetched
            return;
        }
        try {
            $sequence = Wagenreihung::fetch(self::getTripNumber($stopover->trip), $stopover->departure_planned);
            $position = 0;
            foreach ($sequence as $vehicle) {
                CarriageSequence::updateOrCreate(
                    [
                        'stopover_id' => $stopover->id,
                        'position'    => $position++,
                    ],
                    [
                        'sequence'       => $vehicle->fahrzeugsektor,
                        'vehicle_type'   => $vehicle->fahrzeugtyp,
                        'vehicle_number' => $vehicle->fahrzeugnummer,
                        'order_number'   => $vehicle->wagenordnungsnummer,
                    ]
                );
            }
        } catch (TrainNotFoundException) {
            // do nothing
        } catch (Throwable $exception) {
            report($exception);
            //This feature is experimental, so just log all errors to improve later.
        }
    }

    private static function getTripNumber(HafasTrip $trip): int {
        return (int) preg_replace("/[^0-9]/", '', $trip->number);
    }
}
