<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\ExportableColumn;
use App\Enum\ExportableFileType;
use App\Exceptions\DataOverflowException;
use App\Http\Controllers\Backend\Export\ExportController as ExportBackend;
use App\Jobs\MonitoredPersonalDataExportJob;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    #[OA\Post(
        path: '/v1/export/gdpr',
        operationId: 'requestGdprExport',
        description: 'Requests a full GDPR data export. The export is processed asynchronously and delivered via email. Only available when the GDPR export feature is enabled for the account. Subject to a per-user cooldown (see `gdprExportCooldown` in the configuration endpoint). The `recentGdprExport` field on the authenticated user resource reflects the last request timestamp.',
        summary: 'Request a GDPR data export',
        security: [['passport' => ['write-exports']], ['token' => ['write-exports']]],
        tags: ['Export'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Export requested successfully – will be delivered by email when ready',
                content: new OA\JsonContent(
                    required: ['message'],
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Export successfully requested.'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Feature not available for this account, or cooldown period has not elapsed'),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
        ],
    )]
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

    #[OA\Post(
        path: '/v1/export/statuses',
        operationId: 'generateStatusExport',
        description: 'Generates a downloadable export of the authenticated user\'s statuses. Supported formats are `pdf`, `csv_human` (human-readable column headings), `csv_machine` (machine-readable column headings), and `json`. The `columns` parameter selects which fields to include and is required for PDF and CSV formats; it is ignored for JSON. The date range may not exceed 365 days, and the result set is capped at 2000 trips.',
        summary: 'Export statuses as PDF, CSV or JSON',
        security: [['passport' => ['write-exports']], ['token' => ['write-exports']]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['from', 'until', 'filetype'],
                properties: [
                    new OA\Property(
                        property: 'from',
                        description: 'Start date of the export period (inclusive)',
                        type: 'string',
                        format: 'date',
                        example: '2024-01-01',
                    ),
                    new OA\Property(
                        property: 'until',
                        description: 'End date of the export period (inclusive)',
                        type: 'string',
                        format: 'date',
                        example: '2024-01-31',
                    ),
                    new OA\Property(
                        property: 'columns',
                        description: 'Columns to include. Required for pdf/csv formats, ignored for json.',
                        type: 'array',
                        items: new OA\Items(ref: ExportableColumn::class),
                    ),
                    new OA\Property(
                        property: 'filetype',
                        ref: ExportableFileType::class,
                        example: 'csv_human',
                    ),
                ],
            ),
        ),
        tags: ['Export'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File attachment ready for download',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/pdf',
                        schema: new OA\Schema(type: 'string', format: 'binary'),
                    ),
                    new OA\MediaType(
                        mediaType: 'text/csv',
                        schema: new OA\Schema(type: 'string', format: 'binary'),
                    ),
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(type: 'string', format: 'binary'),
                    ),
                ],
            ),
            new OA\Response(response: 400, description: 'Date range exceeds the 365-day maximum'),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 406, description: 'Result set exceeds the 2000-trip limit – narrow the date range'),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function generateStatusExport(Request $request): JsonResponse|StreamedResponse|Response|RedirectResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date', 'before_or_equal:until'],
            'until' => ['required', 'date', 'after_or_equal:from'],
            'columns.*' => ['required', Rule::enum(ExportableColumn::class)],
            'filetype' => [
                'required',
                Rule::enum(ExportableFileType::class),
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
