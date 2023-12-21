<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\Stats\YearInReviewController as YearInReviewBackend;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YearInReviewController extends Controller
{

    /**
     * Returns the year in review for the given year and authenticated user
     *
     * @param Request $request with token and year
     *
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse {
        if (config('trwl.year_in_review_active') === false) {
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
