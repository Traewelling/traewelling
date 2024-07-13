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
     *      tags={"User", "Status", "Events"},
     *      security={{"passport": {}}, {"token": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"subject_type", "subject_id", "reason"},
     *              @OA\Property(property="subject_type", type="string", enum={"Event", "Status", "User"}, example="Status"),
     *              @OA\Property(property="subject_id", type="integer", example=1),
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
                                            'subject_type' => ['required', new Enum(ReportableSubject::class)],
                                            'subject_id'   => ['required', 'integer', 'min:1'],
                                            'reason'       => ['required', new Enum(ReportReason::class)],
                                            'description'  => ['nullable', 'string'],
                                        ]);

        (new ReportRepository())->createReport(
            subjectType: ReportableSubject::fromValue($validated['subject_type']),
            subjectId:   $validated['subject_id'],
            reason:      ReportReason::fromValue($validated['reason']),
            description: $validated['description'],
            reporter:    auth()->user()
        );

        return response()->noContent(201);
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
