<?php

namespace App\Http\Controllers\Backend\Export\Format;

use App\Http\Controllers\Backend\Export\ExportController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

abstract class JsonExportController extends Controller
{

    public static function generateExport(User $user, Carbon $from, Carbon $to): string {
        $preparedData = ExportController::getExportableStatuses($user, $from, $to)->map(function($status) {
            return [
                'status_id' => $status->id,
                'journey'   => [
                    'departure' => [
                        'departurePlanned' => $status->trainCheckin->origin_stopover->departure_planned?->toIso8601String(),
                        'departureReal'    => $status->trainCheckin->origin_stopover->departure_real?->toIso8601String(),
                        'station'          => [
                            'type' => 'stop',
                            'id'   => $status->trainCheckin->Origin->ibnr,
                            'name' => $status->trainCheckin->Origin->name,
                        ]
                    ],
                    'arrival'   => [
                        'arrivalPlanned' => $status->trainCheckin->origin_stopover->arrival_planned?->toIso8601String(),
                        'arrivalReal'    => $status->trainCheckin->origin_stopover->arrival_real?->toIso8601String(),
                        'station'        => [
                            'type' => 'stop',
                            'id'   => $status->trainCheckin->Destination->ibnr,
                            'name' => $status->trainCheckin->Destination->name,
                        ]
                    ],
                    'route'     => [
                        'line'        => $status->trainCheckin->HafasTrip->linename,
                        'operator'    => [
                            'name' => $status->trainCheckin->HafasTrip->operator?->name,
                        ],
                        'origin'      => [
                            'type' => 'stop',
                            'id'   => $status->trainCheckin->HafasTrip->originStation->ibnr,
                            'name' => $status->trainCheckin->HafasTrip->originStation->name,
                        ],
                        'destination' => [
                            'type' => 'stop',
                            'id'   => $status->trainCheckin->HafasTrip->destinationStation->ibnr,
                            'name' => $status->trainCheckin->HafasTrip->destinationStation->name,
                        ],
                    ],
                ]
            ];
        });
        return json_encode([
                               'meta' => [
                                   'user'       => [
                                       'id'       => $user->id,
                                       'username' => $user->username,
                                   ],
                                   'from'       => $from->toIso8601String(),
                                   'to'         => $to->toIso8601String(),
                                   'exportedAt' => Carbon::now()->toIso8601String(),
                               ],
                               'data' => $preparedData,
                           ]);
    }

}
