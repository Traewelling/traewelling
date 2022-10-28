<?php

namespace App\Http\Controllers\Backend\Export\Format;

use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\ExportController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Closure;

abstract class CsvExportController extends Controller
{

    /**
     * @throws DataOverflowException
     */
    public static function generateExport(User $user, Carbon $timestampFrom, Carbon $timestampTo): Closure {
        $data = ExportController::getExportableStatuses($user, $timestampFrom, $timestampTo);

        return static function() use ($data) {
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
                               __('export.title.origin.time.real'),
                               __('export.title.destination.location'),
                               __('export.title.destination.coordinates'),
                               __('export.title.destination.time'),
                               __('export.title.destination.time.real'),
                               __('export.title.travel-time'),
                               __('export.title.kilometer'),
                               __('export.title.points'),
                               __('export.title.status'),
                               __('export.title.type'),
                           ],
                separator: "\t",
            );
            foreach ($data as $status) {
                $row = [
                    $status->id,
                    $status->trainCheckin->HafasTrip->category->value,
                    $status->trainCheckin->HafasTrip->linename,
                    $status->trainCheckin->Origin->name,
                    $status->trainCheckin->Origin->latitude . ', ' . $status->trainCheckin->Origin->longitude,
                    $status->trainCheckin->origin_stopover->departure_planned?->toIso8601String(),
                    $status->trainCheckin->origin_stopover->departure?->toIso8601String(),
                    $status->trainCheckin->Destination->name,
                    $status->trainCheckin->Destination->latitude . ', ' . $status->trainCheckin->Destination->longitude,
                    $status->trainCheckin->destination_stopover->arrival_planned?->toIso8601String(),
                    $status->trainCheckin->destination_stopover->arrival?->toIso8601String(),
                    $status->trainCheckin->duration,
                    number($status->trainCheckin->distance / 1000),
                    $status->trainCheckin->points,
                    $status->body,
                    $status->business->value,
                ];

                fputcsv($fileStream, $row, "\t");
            }
            fclose($fileStream);
        };
    }
}
