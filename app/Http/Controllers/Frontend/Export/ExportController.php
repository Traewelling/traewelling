<?php

namespace App\Http\Controllers\Frontend\Export;

use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Http\Controllers\Backend\Export\Format\CsvExportController;
use App\Http\Controllers\Backend\Export\Format\JsonExportController;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function renderForm(): View {
        return view('export')->with([
                                        'begin_of_month' => Carbon::now()->firstOfMonth()->format('Y-m-d'),
                                        'end_of_month'   => Carbon::now()->lastOfMonth()->format('Y-m-d')
                                    ]);
    }

    public function renderExport(Request $request) {
        $validated = $request->validate([
                                            'begin'    => ['required', 'date', 'before_or_equal:end'],
                                            'end'      => ['required', 'date', 'after_or_equal:begin'],
                                            'filetype' => ['required', Rule::in(['json', 'csv', 'pdf'])],
                                        ]);

        $begin = Carbon::parse($validated['begin']);
        $end   = Carbon::parse($validated['end']);

        if ($validated['filetype'] == 'json') {
            return $this->exportJson($begin, $end);
        } elseif ($validated['filetype'] == 'pdf') {
            return $this->exportPdf($begin, $end);
        } elseif ($validated['filetype'] == 'csv') {
            return $this->exportCsv($begin, $end);
        }
    }

    private function exportJson(Carbon $begin, Carbon $end): JsonResponse {
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


    private function exportPdf(Carbon $begin, Carbon $end) {
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

    private function exportCsv(Carbon $begin, Carbon $end): StreamedResponse {
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
