<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\Stats\YearInReviewController as YearInReviewBackend;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;

class YearInReviewController extends Controller
{

    /**
     * Returns the year in review for the given year and authenticated user
     * !!! THIS IS NOT AN PUBLIC API ENDPOINT !!!
     * You'll need to provide a private token to access this endpoint.
     *
     * @param Request $request with token and year
     *
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'token' => ['required', 'string'],
                                            'year'  => ['required', 'integer', 'min:2019', 'max:' . Date::now()->year],
                                        ]);

        try {
            if (Crypt::decrypt($validated['token']) !== config('auth.api.year-in-review-token')) {
                throw new AuthorizationException();
            }
        } catch (DecryptException|AuthorizationException) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return response()->json(YearInReviewBackend::get(auth()->user(), (int) $validated['year']));
    }
}
