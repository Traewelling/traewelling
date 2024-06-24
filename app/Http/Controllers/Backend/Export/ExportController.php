<?php

namespace App\Http\Controllers\Backend\Export;

use App\Enum\ExportableColumn;
use App\Enum\StatusTagKey;
use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\Format\JsonExportController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
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
                                     //'checkin.trip.stopovers', TODO: This eager load is doing weird things. Some Trips aren't loaded and this throws some http 500. Loading this manually is working.
                                     'checkin.originStopover.station',
                                     'checkin.destinationStopover.station',
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
    public static function generateExport(
        Carbon $from,
        Carbon $until,
        array  $columns,
        string $filetype
    ): HttpResponse|StreamedResponse {
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
            unset($columns['sum']);
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

    private static function getExportMapping(Status $status, ExportableColumn $column) {

        switch ($column) {
            case ExportableColumn::STATUS_ID:
                return $status->id;
            case ExportableColumn::JOURNEY_TYPE:
                return $status->checkin->trip->category->value;
            case ExportableColumn::LINE_NAME:
                return $status->checkin->trip->linename;
            case ExportableColumn::JOURNEY_NUMBER:
                return $status->checkin->trip->journey_number;
            case ExportableColumn::ORIGIN_NAME:
                return $status->checkin->originStopover->station->name;
            case ExportableColumn::ORIGIN_COORDINATES:
                return $status->checkin->originStopover->station->latitude
                       . ',' . $status->checkin->originStopover->station->longitude;
            case ExportableColumn::DEPARTURE_PLANNED:
                return $status->checkin->originStopover?->departure_planned?->toIso8601String();
            case ExportableColumn::DEPARTURE_REAL:
                return $status->checkin->originStopover?->departure?->toIso8601String();
            case ExportableColumn::DESTINATION_NAME:
                return $status->checkin->destinationStopover->station->name;
            case ExportableColumn::DESTINATION_COORDINATES:
                return $status->checkin->destinationStopover->station->latitude
                       . ',' . $status->checkin->destinationStopover->station->longitude;
            case ExportableColumn::ARRIVAL_PLANNED:
                return $status->checkin->destinationStopover?->arrival_planned?->toIso8601String();
            case ExportableColumn::ARRIVAL_REAL:
                return $status->checkin->destinationStopover?->arrival?->toIso8601String();
            case ExportableColumn::DURATION:
                return $status->checkin->duration;
            case ExportableColumn::DISTANCE:
                return $status->checkin->distance;
            case ExportableColumn::POINTS:
                return $status->checkin->points;
            case ExportableColumn::BODY:
                return $status->body;
            case ExportableColumn::TRAVEL_TYPE:
                return $status->business->name;
            case ExportableColumn::OPERATOR:
                return $status->checkin->trip?->operator?->name;
            case ExportableColumn::STATUS_TAGS:
                $tags = [];
                foreach ($status->tags as $tag) {
                    $tags[$tag->key] = $tag->value;
                }
                return $tags;
            default:
                throw new InvalidArgumentException('unsupported column');
        }
    }

    /**
     * @throws DataOverflowException
     */
    public static function getExportData(Carbon $timestampFrom, Carbon $timestampTo, array &$columns): array {
        $statuses    = self::getExportableStatuses(auth()->user(), $timestampFrom, $timestampTo);
        $data        = [];
        $tagKeys     = [];
        $statusTags  = [];
        $distanceSum = 0;
        $durationSum = 0;
        $pointsSum   = 0;
        foreach ($statuses as $key => $status) {
            $row  = [];
            $tags = [];

            foreach ($columns as $column) {
                if (!($column instanceof ExportableColumn)) {
                    continue;
                }
                if ($column === ExportableColumn::STATUS_TAGS) {
                    $tags = self::getExportMapping($status, $column);
                    foreach ($tags as $tag => $value) {
                        if (!in_array($tag, $tagKeys, true)) {
                            $tagKeys[] = $tag;
                        }
                    }
                    $statusTags[$key] = $tags;

                    continue;
                }
                if ($column === ExportableColumn::DISTANCE) {
                    $distanceSum += $status->checkin->distance;
                }
                if ($column === ExportableColumn::DURATION) {
                    $durationSum += $status->checkin->duration;
                }
                if ($column === ExportableColumn::POINTS) {
                    $pointsSum += $status->checkin->points;
                }
                $row[$column->value] = self::getExportMapping($status, $column);
            }

            $data[$key] = $row;
        }

        foreach ($statusTags as $key => $tags) {
            foreach ($tagKeys as $tagKey) {
                $data[$key][$tagKey] = $tags[$tagKey] ?? null;
            }
        }

        $data['sum'] = [
            ExportableColumn::DISTANCE->value => $distanceSum,
            ExportableColumn::DURATION->value => $durationSum,
            ExportableColumn::POINTS->value   => $pointsSum,
        ];

        array_push($columns, ...$tagKeys);

        if (($key = array_search(ExportableColumn::STATUS_TAGS, $columns, true)) !== false) {
            unset($columns[$key]);
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

    private static function exportPdf(Carbon $from, Carbon $until, array $columns, array $data): HttpResponse {
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

    private static function exportCsv(
        Carbon $from,
        Carbon $until,
        array  $columns,
        array  $data,
        bool   $humanReadableHeadings = false
    ): StreamedResponse {
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
                if ($humanReadableHeadings) {
                    $stringColumns[] = self::getColumnTitle($column);
                    continue;
                }
                $stringColumns[] = $column->value ?? $column;
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

    public static function getColumnTitle(ExportableColumn|string $column): string {
        if ($column instanceof ExportableColumn) {
            return $column->title();
        }

        $key = StatusTagKey::tryFrom($column);
        return $key?->title() ?? $column;
    }

    public static function formatExportableColumn(ExportableColumn|string $column, mixed $value): string {
        if (empty($value)) {
            return '';
        }
        if (!$column instanceof ExportableColumn) {
            $column = ExportableColumn::tryFrom($column);
        }

        return match ($column) {
            ExportableColumn::ARRIVAL_PLANNED,
            ExportableColumn::ARRIVAL_REAL,
            ExportableColumn::DEPARTURE_PLANNED,
            ExportableColumn::DEPARTURE_REAL => userTime($value, __('datetime-format')),
            ExportableColumn::DISTANCE       => number($value / 1000),
            ExportableColumn::DURATION       => durationToSpan(secondsToDuration($value * 60)),
            ExportableColumn::POINTS         => number($value, 0),
            default                          => (string) $value,
        };
    }
}
