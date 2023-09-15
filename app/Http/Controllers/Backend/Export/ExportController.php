<?php

namespace App\Http\Controllers\Backend\Export;

use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Http\Controllers\Backend\Export\Format\CsvExportController;
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
    public static function generateExport(Carbon $from, Carbon $until, string $filetype) {
        if ($filetype === 'json') {
            return self::exportJson($from, $until);
        }

        if ($filetype === 'pdf') {
            return self::exportPdf($from, $until);
        }

        if ($filetype === 'csv') {
            return self::exportCsv($from, $until);
        }

        throw new InvalidArgumentException('unsupported filetype');
    }

    /**
     * @throws DataOverflowException
     */
    private static function exportJson(Carbon $begin, Carbon $end): JsonResponse {
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

    /**
     * @throws DataOverflowException
     */
    private static function exportPdf(Carbon $begin, Carbon $end): \Illuminate\Http\Response {
        $statuses = ExportBackend::getExportableStatuses(auth()->user(), $begin, $end);

        return Pdf::loadView('pdf.export-template', [
            'statuses'     => $statuses,
            'begin'        => $begin,
            'end'          => $end,
            'sum_duration' => $statuses->sum('trainCheckin.duration'),
            'sum_distance' => $statuses->sum('trainCheckin.distance') / 1000,
        ])
                  ->setPaper('a4', 'landscape')
                  ->download(
                      sprintf(
                          'Traewelling_export_%s_to_%s.pdf',
                          $begin->format('Y-m-d'),
                          $end->format('Y-m-d')
                      )
                  );
    }

    /**
     * @throws DataOverflowException
     */
    private static function exportCsv(Carbon $begin, Carbon $end): StreamedResponse {
        $headers      = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => sprintf(
                'attachment; filename="Traewelling_export_%s_to_%s.csv"',
                $begin->format('Y-m-d'),
                $end->format('Y-m-d')
            ),
            'Expires'             => '0',
            'Pragma'              => 'public',
        ];
        $exportStream = CsvExportController::generateExport(auth()->user(), $begin, $end);
        return Response::stream($exportStream, 200, $headers);
    }
}
