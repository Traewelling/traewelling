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
        $statuses = Status::join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                          ->where('statuses.user_id', $user->id)
                          ->where('train_checkins.departure', '>=', $timestampFrom->startOfDay()->toIso8601String())
                          ->where('train_checkins.departure', '<=', $timestampTo->endOfDay()->toIso8601String())
                          ->select(['statuses.*'])
                          ->limit(2001)
                          ->get();
        if ($statuses->count() >= 2001) {
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
        return Pdf::loadView('pdf.export-template', [
            'statuses' => ExportBackend::getExportableStatuses(auth()->user(), $begin, $end),
            'begin'    => $begin,
            'end'      => $end
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
