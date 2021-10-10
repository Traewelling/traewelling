<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Resources\LeaderboardUserResource;
use App\Http\Resources\StatisticsGlobalData;
use App\Http\Resources\StatisticsTravelPurposeResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatisticsController extends ResponseController
{
    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboard(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard());
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboardByDistance(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard(orderBy: 'distance'));
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboardFriends(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard(onlyFollowings: true));
    }

    /**
     * @param string $date
     *
     * @return AnonymousResourceCollection
     */
    public function leaderboardForMonth(string $date): AnonymousResourceCollection {
        $date = Carbon::parse($date);
        return LeaderboardUserResource::collection(LeaderboardBackend::getMonthlyLeaderboard(date: $date));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getPersonalStatistics(Request $request): JsonResponse {
        $validated = $request->validate([
                                            'from'  => ['nullable', 'date'],
                                            'until' => ['nullable', 'date', 'after_or_equal:from']
                                        ]);

        $from  = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subWeeks(4);
        $until = isset($validated['until']) ? Carbon::parse($validated['until']) : Carbon::now();

        $purposes   = StatisticsTravelPurposeResource::collection(
            StatisticBackend::getTravelPurposes(user: auth()->user(), from: $from, until: $until)
        );
        $categories = StatisticBackend::getTopTravelCategoryByUser(user: auth()->user(), from: $from, until: $until);
        $operators  = StatisticBackend::getTopTripOperatorByUser(user: auth()->user(), from: $from, until: $until);
        $travelTime = StatisticBackend::getDailyTravelTimeByUser(user: auth()->user(), from: $from, until: $until);

        $returnData = [
            'purpose'    => $purposes,
            'categories' => $categories,
            'operators'  => $operators,
            'time'       => $travelTime
        ];

        $additionalData = [
            'meta' => [
                'from'  => $from,
                'until' => $until
            ]
        ];

        return $this->sendv1Response(data: $returnData, additional: $additionalData);
    }

    public function getGlobalStatistics(): JsonResponse {
        $from  = Carbon::now()->subWeeks(4);
        $until = Carbon::now();

        $data = new StatisticsGlobalData(StatisticBackend::getGlobalCheckInStats(from: $from, until: $until));

        $additionalData = [
            'meta' => [
                'from'  => $from,
                'until' => $until
            ]
        ];

        return $this->sendv1Response(data: $data, additional: $additionalData);
    }

    public function generateTravelExport(Request $request): JsonResponse|StreamedResponse|Response {
        $validated = $request->validate([
                                            'from'     => ['required', 'date', 'before_or_equal:until'],
                                            'until'    => ['required', 'date', 'after_or_equal:from'],
                                            'filetype' => ['required', Rule::in(['json', 'csv', 'pdf'])],
                                        ]);

        return StatusBackend::ExportStatuses(
            startDate: Carbon::parse($validated['from']),
            endDate:   Carbon::parse($validated['until']),
            fileType:  $request->input('filetype')
        );
    }
}
