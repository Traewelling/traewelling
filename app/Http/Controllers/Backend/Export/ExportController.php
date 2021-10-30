<?php

namespace App\Http\Controllers\Backend\Export;

use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Http\Controllers\Backend\Export\Format\CsvExportController;
use App\Http\Controllers\Backend\Export\Format\JsonExportController;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ExportController extends Controller
{

    public static function getExportableStatuses(User $user, Carbon $timestampFrom, Carbon $timestampTo): Collection {
        return Status::join('train_checkins', 'statuses.id', '=', 'train_checkins.status_id')
                     ->where('statuses.user_id', $user->id)
                     ->where('train_checkins.departure', '>=', $timestampFrom->startOfDay()->toIso8601String())
                     ->where('train_checkins.departure', '<=', $timestampTo->endOfDay()->toIso8601String())
                     ->select(['statuses.*'])
                     ->get();
    }

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

    private static function exportPdf(Carbon $begin, Carbon $end) {
        return PDF::loadView('pdf.export-template', [
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
