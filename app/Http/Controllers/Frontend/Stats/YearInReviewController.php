<?php

namespace App\Http\Controllers\Frontend\Stats;

use App\Http\Controllers\API\v1\Controller;
use App\Http\Controllers\Backend\Stats\YearInReviewController as YearInReviewBackend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

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
        $validated = $request->validate([
                                            'year' => ['nullable', 'integer', 'min:2019', 'max:' . Date::now()->year],
                                        ]);

        if (isset($validated['year'])) {
            $year = $validated['year'];
        } else {
            $year = Date::now()->year;
            if (Date::now()->month < 3) {
                $year--;
            }
        }

        return response()->json(YearInReviewBackend::generate(auth()->user(), $year));
    }
}
