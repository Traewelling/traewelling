<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\ExportableColumn;
use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Jobs\MonitoredPersonalDataExportJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function requestGdprExport(Request $request): JsonResponse|Response|RedirectResponse {
        $user = $request->user();

        if ($user->recent_gdpr_export && $user->recent_gdpr_export->diffInDays(now()) < 30) {
            return response()->json(['error' => __('export.error.gdpr-time', ['date' => userTime($user->recent_gdpr_export)])], 400);
        }

        $user->update(['recent_gdpr_export' => now()]);

        dispatch(new MonitoredPersonalDataExportJob($user));

        return response()->json(['message' => __('export.requested')], 202);
    }

    public function generateStatusExport(Request $request): JsonResponse|StreamedResponse|Response|RedirectResponse {
        $validated = $request->validate([
                                            'from'      => ['required', 'date', 'before_or_equal:until'],
                                            'until'     => ['required', 'date', 'after_or_equal:from'],
                                            'columns.*' => ['required', Rule::enum(ExportableColumn::class)],
                                            'filetype'  => ['required', Rule::in(['pdf', 'csv_human', 'csv_machine', 'json'])],
                                        ]);

        $from  = Carbon::parse($validated['from']);
        $until = Carbon::parse($validated['until']);
        if ($from->diffInDays($until) > 365) {
            return response()->json(['error' => __('export.error.time')], 400);
        }

        if ($validated['filetype'] === 'json') {
            return ExportBackend::exportJson($from, $until);
        }

        $columns = [];
        foreach ($validated['columns'] ?? [] as $column) {
            $columns[] = ExportableColumn::from($column);
        }
        if (empty($columns)) {
            $columns = ExportableColumn::cases();
        }

        try {
            return ExportBackend::generateExport(
                from:     $from,
                until:    $until,
                columns:  $columns,
                filetype: $validated['filetype']
            );
        } catch (DataOverflowException) {
            return response()->json(['error' => __('export.error.amount')], 406);
        }
    }
}
