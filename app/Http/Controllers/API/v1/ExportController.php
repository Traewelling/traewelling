<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\ExportableColumn;
use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
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
            return back()->with('error', __('export.error.time'));
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
            return back()->with('error', __('export.error.amount'));
        }
    }
}
