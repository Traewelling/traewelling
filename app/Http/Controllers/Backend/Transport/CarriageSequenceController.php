<?php

namespace App\Http\Controllers\Backend\Transport;

use App\Enum\HafasTravelType;
use App\Http\Controllers\Controller;
use App\Models\HafasTrip;
use App\Models\StopoverVehicleSequence;
use App\Models\TrainStopover;
use App\Models\Vehicle;
use App\Models\VehicleGroup;
use K118\DB\Exceptions\TrainNotFoundException;
use K118\DB\Wagenreihung;

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
            $vehicleGroups = Wagenreihung::fetch(self::getTripNumber($stopover->trip), $stopover->departure_planned);
            $position      = 0;
            foreach ($vehicleGroups as $vehicleGroup) {
                $modelVehicleGroup = VehicleGroup::updateOrCreate(['name' => $vehicleGroup->fahrzeuggruppebezeichnung]);

                foreach ($vehicleGroup->vehicles as $vehicle) {
                    $modelVehicle = Vehicle::updateOrCreate(
                        ['name' => $vehicle->fahrzeugnummer],
                        [
                            'vehicle_group_id' => $modelVehicleGroup->id,
                            'classification'   => $vehicle->fahrzeugtyp,
                        ]
                    );

                    StopoverVehicleSequence::updateOrCreate(
                        [
                            'stopover_id' => $stopover->id,
                            'position'    => $position++,
                        ],
                        [
                            'sequence'     => $vehicle->fahrzeugsektor,
                            'vehicle_id'   => $modelVehicle->id,
                            'order_number' => $vehicle->wagenordnungsnummer,
                        ]
                    );
                }

            }
        } catch (TrainNotFoundException) {
            // do nothing
        } //catch (Throwable $exception) {
        //    report($exception);
        //This feature is experimental, so just log all errors to improve later.
        //}
    }

    private static function getTripNumber(HafasTrip $trip): int {
        return (int) preg_replace("/[^0-9]/", '', $trip->number);
    }
}
