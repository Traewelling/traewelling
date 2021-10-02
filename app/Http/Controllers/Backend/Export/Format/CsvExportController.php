<?php

namespace App\Http\Controllers\Backend\Export\Format;

use App\Http\Controllers\Backend\Export\ExportController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Closure;

abstract class CsvExportController extends Controller
{

    public static function generateExport(User $user, Carbon $from, Carbon $to): Closure {
        $data = ExportController::getExportableStatuses($user, $from, $to);

        return function() use ($data) {
            $fileStream = fopen('php://output', 'w');
            fputcsv(
                stream:    $fileStream,
                fields:    [
                               __('export.title.status-id'),
                               __('export.title.train-type'),
                               __('export.title.train-number'),
                               __('export.title.origin.location'),
                               __('export.title.origin.coordinates'),
                               __('export.title.origin.time'),
                               __('export.title.origin.time'),
                               __('export.title.destination.location'),
                               __('export.title.destination.coordinates'),
                               __('export.title.destination.time'),
                               __('export.title.destination.time'),
                               __('export.title.travel-time'),
                               __('export.title.kilometer'),
                               __('export.title.points'),
                               __('export.title.status'),
                               __('export.title.type'),
                           ],
                separator: "\t"
            );
            foreach ($data as $status) {

                $row = [
                    $status->id,
                    $status->trainCheckin->HafasTrip->category, //traintype
                    $status->trainCheckin->HafasTrip->lineName, //trainnumber
                    $status->trainCheckin->Origin->name,//origin name
                    $status->trainCheckin->Origin->latitude . ', ' . $status->trainCheckin->Origin->longitude, //origin coordinates
                    $status->trainCheckin->origin_stopover->departure_planned?->toIso8601String(),//origin time planned
                    $status->trainCheckin->origin_stopover->departure?->toIso8601String(),//origin time real
                    $status->trainCheckin->Destination->name,//destination name
                    $status->trainCheckin->Destination->latitude . ', ' . $status->trainCheckin->Destination->longitude, //destination coordinates
                    $status->trainCheckin->destination_stopover->arrival_planned?->toIso8601String(),//destination time planned
                    $status->trainCheckin->destination_stopover->arrival?->toIso8601String(),//destination time real
                    $status->trainCheckin->duration, //duration
                    $status->trainCheckin->distance,//distance
                    $status->points,//points
                    $status->body,//status?
                    $status->business//type?
                ];

                fputcsv($fileStream, $row, "\t");
            }
            fclose($fileStream);
        };
    }

}
