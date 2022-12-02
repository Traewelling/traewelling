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
                                            'year'  => ['required', 'integer', 'min:2019', 'max:' . Date::now()->year],
                                        ]);

        return response()->json(YearInReviewBackend::get(auth()->user(), (int) $validated['year']));
    }
}
