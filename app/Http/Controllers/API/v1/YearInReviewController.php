<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\Stats\YearInReviewController as YearInReviewBackend;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YearInReviewController extends Controller
{

    /**
     * @OA\Get(
     *      path="/year-in-review",
     *      operationId="getYearInReview",
     *      tags={"Statistics"},
     *      summary="Returns the year in review for the given year and authenticated user",
     *      description="Please note: This endpoint is only available when the year in review feature is enabled in the backend configuration. There is no full documentation - this endpoint may change every year.",
     *      @OA\Response(
     *          response=200,
     *          description="JSON object with the year in review data. The structure of the object may change every year. There is no full documentation at this point.",
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=403, description="Year in review is not active"),
     *       security={
     *          {"passport": {"create-statuses"}}, {"token": {}}
     *       }
     *     )
     */
    public function show(Request $request): JsonResponse {
        if (config('trwl.year_in_review.backend') === false) {
            return $this->sendError('Year in review is not active', 403);
        }

        $validated = $request->validate([
                                            'year' => ['nullable', 'integer', 'min:2019', 'max:' . Carbon::now()->year],
                                        ]);

        if (isset($validated['year'])) {
            $year = $validated['year'];
        } else {
            $year = Carbon::now()->year;
            if (Carbon::now()->month < 3) {
                $year--;
            }
        }

        return response()->json(YearInReviewBackend::get(auth()->user(), $year));
    }
}
