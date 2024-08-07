<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\Report\ReportableSubject;
use App\Enum\Report\ReportReason;
use App\Enum\Report\ReportStatus;
use App\Models\Report;
use App\Repositories\ReportRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

class ReportController extends Controller
{
    /**
     * @OA\Post(
     *      path="/report",
     *      operationId="report",
     *      summary="Report a Status, Event or User to the admins.",
     *      tags={"Report"},
     *      security={{"passport": {}}, {"token": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"subjectType", "subjectId", "reason"},
     *              @OA\Property(property="subjectType", type="string", enum={"Event", "Status", "User"}, example="Status"),
     *              @OA\Property(property="subjectId", type="integer", example=1),
     *              @OA\Property(property="reason", type="string", enum={"inappropriate", "implausible", "spam", "illegal", "other"}, example="inappropriate"),
     *              @OA\Property(property="description", type="string", example="The status is inappropriate because...", nullable=true),
     *          ),
     *      ),
     *      @OA\Response(response=201, description="The report was successfully created."),
     *      @OA\Response(response=401, description="The user is not authenticated."),
     *      @OA\Response(response=422, description="The given data was invalid."),
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request): Response {
        $validated = $request->validate([
                                            'subject_type' => [new Enum(ReportableSubject::class)], // Todo: Remove after 2023-08-17
                                            'subjectType'  => ['required_without:subject_type', new Enum(ReportableSubject::class)],
                                            'subjectId'    => ['required_without:subject_id', 'integer', 'min:1'],
                                            'subject_id'   => ['integer', 'min:1'], // Todo: Remove after 2023-08-17
                                            'reason'       => ['required', new Enum(ReportReason::class)],
                                            'description'  => ['nullable', 'string'],
                                        ]);

        $subjectType = $validated['subjectType'] ?? $validated['subject_type']; // Todo: Remove after 2023-08-17
        $subjectId   = $validated['subjectId'] ?? $validated['subject_id'];     // Todo: Remove after 2023-08-17

        (new ReportRepository())->createReport(
            subjectType: ReportableSubject::from($subjectType),
            subjectId:   $subjectId,
            reason:      ReportReason::from($validated['reason']),
            description: $validated['description'],
            reporter:    auth()->user()
        );
        
        return response()->noContent(201, ['Content-Type' => 'application/json']);
    }

    /**
     * Admin only - no public documentation.
     *
     * @param Request $request
     * @param int     $reportId
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, int $reportId): JsonResponse {
        $report = Report::findOrFail($reportId);
        $this->authorize('update', $report);

        $validated = $request->validate([
                                            'status'      => ['required', new Enum(ReportStatus::class)],
                                            'description' => ['nullable', 'string', 'max:255'],
                                        ]);

        $logger = activity()->causedBy(auth()->user())
                            ->performedOn($report);
        if ($validated['status'] !== $report->status->value) {
            $logger->withProperties([
                                        'attributes' => [
                                            'status' => $validated['status'],
                                        ],
                                        'old'        => [
                                            'status' => $report->status,
                                        ],
                                    ]);
        }
        $logger->log($validated['description'] ?? '');

        $report->update(['status' => $validated['status']]);

        return $this->sendResponse(
            data: 'Report updated.'
        );
    }
}
