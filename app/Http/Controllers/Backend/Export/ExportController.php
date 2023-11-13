<?php

namespace App\Http\Controllers\Backend\Export;

use App\Enum\ExportableColumn;
use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\Format\JsonExportController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ExportController extends Controller
{

    /**
     * @throws DataOverflowException If too many results are given.
     */
    public static function getExportableStatuses(User $user, Carbon $timestampFrom, Carbon $timestampTo): Collection {
        $statuses = Status::with([
                                     //'trainCheckin.HafasTrip.stopovers', TODO: This eager load is doing weird things. Some HafasTrips aren't loaded and this throws some http 500. Loading this manually is working.
                                     'trainCheckin.originStation',
                                     'trainCheckin.destinationStation',
                                 ])
                          ->join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                          ->where('statuses.user_id', $user->id)
                          ->where('train_checkins.departure', '>=', $timestampFrom->startOfDay())
                          ->where('train_checkins.departure', '<=', $timestampTo->endOfDay())
                          ->select(['statuses.*'])
                          ->limit(2001)
                          ->get();
        // A user should only be able to export 2000 statuses at once to avoid memory
        // overflows. Thus, if the database returns 2001 entries (which is the limit),
        // there are `>2000` statuses in this time frame and the user must choose a
        // smaller time frame.
        if ($statuses->count() === 2001) {
            throw new DataOverflowException();
        }
        return $statuses;
    }

    /**
     * @throws DataOverflowException
     */
    public static function generateExport(Carbon $from, Carbon $until, array $columns, string $filetype): \Illuminate\Http\Response|StreamedResponse {
        $data = self::getExportData($from, $until, $columns);

        if ($filetype === 'pdf') {
            return self::exportPdf(
                from:    $from,
                until:   $until,
                columns: $columns,
                data:    $data,
            );
        }

        if ($filetype === 'csv_human' || $filetype === 'csv_machine') {
            return self::exportCsv(
                from:                  $from,
                until:                 $until,
                columns:               $columns,
                data:                  $data,
                humanReadableHeadings: $filetype === 'csv_human',
            );
        }

        throw new InvalidArgumentException('unsupported filetype');
    }

    /**
     * @throws DataOverflowException
     */
    public static function getExportData(Carbon $timestampFrom, Carbon $timestampTo, array &$columns): array {
        $statuses = self::getExportableStatuses(auth()->user(), $timestampFrom, $timestampTo);
        $data     = [];
        foreach ($statuses as $status) {
            $row = [];

            if (in_array(ExportableColumn::STATUS_ID, $columns, true)) {
                $row[ExportableColumn::STATUS_ID->value] = $status->id;
            }
            if (in_array(ExportableColumn::JOURNEY_TYPE, $columns, true)) {
                $row[ExportableColumn::JOURNEY_TYPE->value] = $status->trainCheckin->HafasTrip->category->value;
            }
            if (in_array(ExportableColumn::LINE_NAME, $columns, true)) {
                $row[ExportableColumn::LINE_NAME->value] = $status->trainCheckin->HafasTrip->linename;
            }
            if (in_array(ExportableColumn::JOURNEY_NUMBER, $columns, true)) {
                $row[ExportableColumn::JOURNEY_NUMBER->value] = $status->trainCheckin->HafasTrip->journey_number;
            }
            if (in_array(ExportableColumn::ORIGIN_NAME, $columns, true)) {
                $row[ExportableColumn::ORIGIN_NAME->value] = $status->trainCheckin->originStation->name;
            }
            if (in_array(ExportableColumn::ORIGIN_COORDINATES, $columns, true)) {
                $row[ExportableColumn::ORIGIN_COORDINATES->value] = $status->trainCheckin->originStation->latitude . ',' . $status->trainCheckin->originStation->longitude;
            }
            if (in_array(ExportableColumn::DEPARTURE_PLANNED, $columns, true)) {
                $row[ExportableColumn::DEPARTURE_PLANNED->value] = $status->trainCheckin->origin_stopover?->departure_planned?->toIso8601String();
            }
            if (in_array(ExportableColumn::DEPARTURE_REAL, $columns, true)) {
                $row[ExportableColumn::DEPARTURE_REAL->value] = $status->trainCheckin->origin_stopover?->departure?->toIso8601String();
            }
            if (in_array(ExportableColumn::DESTINATION_NAME, $columns, true)) {
                $row[ExportableColumn::DESTINATION_NAME->value] = $status->trainCheckin->destinationStation->name;
            }
            if (in_array(ExportableColumn::DESTINATION_COORDINATES, $columns, true)) {
                $row[ExportableColumn::DESTINATION_COORDINATES->value] = $status->trainCheckin->destinationStation->latitude . ',' . $status->trainCheckin->destinationStation->longitude;
            }
            if (in_array(ExportableColumn::ARRIVAL_PLANNED, $columns, true)) {
                $row[ExportableColumn::ARRIVAL_PLANNED->value] = $status->trainCheckin->destination_stopover?->arrival_planned?->toIso8601String();
            }
            if (in_array(ExportableColumn::ARRIVAL_REAL, $columns, true)) {
                $row[ExportableColumn::ARRIVAL_REAL->value] = $status->trainCheckin->destination_stopover?->arrival?->toIso8601String();
            }
            if (in_array(ExportableColumn::DURATION, $columns, true)) {
                $row[ExportableColumn::DURATION->value] = $status->trainCheckin->duration;
            }
            if (in_array(ExportableColumn::DISTANCE, $columns, true)) {
                $row[ExportableColumn::DISTANCE->value] = $status->trainCheckin->distance;
            }
            if (in_array(ExportableColumn::POINTS, $columns, true)) {
                $row[ExportableColumn::POINTS->value] = $status->trainCheckin->points;
            }
            if (in_array(ExportableColumn::BODY, $columns, true)) {
                $row[ExportableColumn::BODY->value] = $status->body;
            }
            if (in_array(ExportableColumn::TRAVEL_TYPE, $columns, true)) {
                $row[ExportableColumn::TRAVEL_TYPE->value] = $status->business->name;
            }
            if (in_array(ExportableColumn::STATUS_TAGS, $columns, true)) {
                //remove generic enum and add status tags
                unset($columns[array_search(ExportableColumn::STATUS_TAGS, $columns, true)]);
                foreach ($status->tags as $tag) {
                    if (!in_array($tag->keyEnum, $columns, true)) {
                        $columns[] = $tag->keyEnum;
                    }
                    $row[$tag->key] = $tag->value;
                }
            }
            if (in_array(ExportableColumn::OPERATOR, $columns, true)) {
                $row[ExportableColumn::OPERATOR->value] = $status->trainCheckin->HafasTrip?->operator?->name;
            }
            $data[] = $row;
        }
        return $data;
    }

    /**
     * @throws DataOverflowException
     */
    public static function exportJson(Carbon $begin, Carbon $end): JsonResponse {
        $headers    = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/json',
            'Content-Disposition' => sprintf(
                'attachment; filename="Traewelling_export_%s_to_%s.json"',
                $begin->format('Y-m-d'),
                $end->format('Y-m-d')
            ),
            'Expires'             => '0',
            'Pragma'              => 'public',
        ];
        $exportData = JsonExportController::generateExport(auth()->user(), $begin, $end);
        return Response::json(data: $exportData, headers: $headers);
    }

    private static function exportPdf(Carbon $from, Carbon $until, array $columns, array $data): \Illuminate\Http\Response {
        return Pdf::loadView('pdf.export-template', [
            'begin'   => $from,
            'end'     => $until,
            'columns' => $columns,
            'data'    => $data,
        ])
                  ->setPaper('a4', 'landscape')
                  ->download(
                      sprintf(
                          'Traewelling_export_%s_to_%s.pdf',
                          $from->format('Y-m-d'),
                          $until->format('Y-m-d')
                      )
                  );
    }

    private static function exportCsv(Carbon $from, Carbon $until, array $columns, array $data, bool $humanReadableHeadings = false): StreamedResponse {
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => sprintf(
                'attachment; filename="Traewelling_export_%s_to_%s.csv"',
                $from->format('Y-m-d'),
                $until->format('Y-m-d')
            ),
            'Expires'             => '0',
            'Pragma'              => 'public',
        ];

        $fileStream = static function() use ($humanReadableHeadings, $columns, $data) {
            $csv           = fopen('php://output', 'w');
            $stringColumns = [];
            foreach ($columns as $column) {
                if($humanReadableHeadings) {
                    $stringColumns[] = $column->title();
                    continue;
                }
                $stringColumns[] = $column->value;
            }
            fputcsv(
                stream: $csv,
                fields: $stringColumns,
            );
            foreach ($data as $row) {
                fputcsv($csv, $row);
            }
            fclose($csv);
        };

        return Response::stream($fileStream, 200, $headers);
    }
}
