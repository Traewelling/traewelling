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
    public function requestGdprExport(Request $request): JsonResponse|Response|RedirectResponse
    {
        $validated = $request->validate([
            'frontend' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();

        if (!(config('trwl.ab_testing.gdpr_export') || $user->hasRole('test-gdpr-export'))) {
            return $this->frontendOrJson($validated, ['error' => __('export.error.gdpr')]);
        }

        if ($user->recent_gdpr_export && $user->recent_gdpr_export->diffInDays(now()) < config('trwl.gdpr_export.days')) {
            return $this->frontendOrJson(
                $validated,
                ['error' => __('export.error.gdpr-time', [
                    'date' => userTime($user->recent_gdpr_export),
                    'days' => config('trwl.gdpr_export.days'),
                ])]
            );
        }

        $user->update(['recent_gdpr_export' => now()]);

        dispatch(new MonitoredPersonalDataExportJob($user));

        return $this->frontendOrJson($validated, ['message' => __('export.requested')], 200);
    }

    public function generateStatusExport(Request $request): JsonResponse|StreamedResponse|Response|RedirectResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date', 'before_or_equal:until'],
            'until' => ['required', 'date', 'after_or_equal:from'],
            'columns.*' => ['required', Rule::enum(ExportableColumn::class)],
            'filetype' => [
                'required',
                Rule::in(['pdf', 'csv_human', 'csv_machine', 'json']),
            ],
            'frontend' => ['nullable', 'boolean'],
        ]);

        $from = Carbon::parse($validated['from']);
        $until = Carbon::parse($validated['until']);
        if ($from->diffInDays($until) > 365) {
            return $this->frontendOrJson($validated, ['error' => __('export.error.time')]);
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
                from: $from,
                until: $until,
                columns: $columns,
                filetype: $validated['filetype']
            );
        } catch (DataOverflowException) {
            return $this->frontendOrJson($validated, ['error' => __('export.error.amount')], 406);
        }
    }

    private function frontendOrJson(array $validated, array $data, int $status = 400): RedirectResponse|JsonResponse
    {
        if (empty($validated['frontend'])) {
            return response()->json($data, $status);
        }

        if (array_key_exists('error', $data)) {
            return redirect('export')->with($data);
        }

        return redirect('export')->with('success', $data['message']);
    }
}
