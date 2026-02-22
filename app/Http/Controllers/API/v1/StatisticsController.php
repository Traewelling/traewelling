<?php

namespace App\Http\Controllers\API\v1;

use App\Dto\GeoJson\Feature;
use App\Dto\GeoJson\FeatureCollection;
use App\Helpers\CacheKey;
use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
use App\Http\Controllers\Backend\Stats\DailyStatsController;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Resources\LeaderboardUserResource;
use App\Http\Resources\StatisticsGlobalData;
use App\Http\Resources\StatisticsTravelPurposeResource;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class StatisticsController extends Controller
{
    private LeaderboardBackend $leaderboardBackend;

    public function __construct(LeaderboardBackend $leaderboard)
    {
        $this->leaderboardBackend = $leaderboard;
    }

    /**
     * @OA\Get(
     *      path="/leaderboard",
     *      operationId="getLeaderboard",
     *      tags={"Leaderboard"},
     *      summary="[Auth optional] Get array of 20 best users",
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="array",
     *
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUserResource"
     *                  )
     *              ),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *       }
     *     )
     */
    public function leaderboard(): AnonymousResourceCollection
    {
        return LeaderboardUserResource::collection($this->leaderboardBackend->getCachedGlobalLeaderboard());
    }

    /**
     * @OA\Get(
     *      path="/leaderboard/distance",
     *      operationId="getLeaderboardByDistance",
     *      tags={"Leaderboard"},
     *      summary="[Auth optional] Get leaderboard array sorted by distance",
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="array",
     *
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUserResource"
     *                  )
     *              ),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function leaderboardByDistance(): AnonymousResourceCollection
    {
        return LeaderboardUserResource::collection($this->leaderboardBackend->getCachedDistanceLeaderboard());
    }

    /**
     * @OA\Get(
     *      path="/leaderboard/friends",
     *      operationId="getLeaderboardByFriends",
     *      tags={"Leaderboard"},
     *      summary="Get friends-leaderboard array sorted",
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="array",
     *
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUserResource"
     *                  )
     *              ),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function leaderboardFriends(): AnonymousResourceCollection
    {
        return LeaderboardUserResource::collection($this->leaderboardBackend->getCachedFriendsLeaderboard());
    }

    /**
     * @OA\Get(
     *      path="/leaderboard/{month}",
     *      operationId="getMonthlyLeaderboard",
     *      tags={"Leaderboard"},
     *      summary="[Auth optional] Get leaderboard array for a specific month",
     *
     *      @OA\Parameter(
     *          name="month",
     *          in="path",
     *          description="Month for the complete leaderboard in Format `YYYY-MM`",
     *          example="2022-04",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="array",
     *
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUserResource"
     *                  )
     *              ),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function leaderboardForMonth(string $date): AnonymousResourceCollection
    {
        $date = Carbon::parse($date);

        return LeaderboardUserResource::collection(LeaderboardBackend::getMonthlyLeaderboard(date: $date));
    }

    /**
     * @OA\Get(
     *     path="/statistics",
     *     operationId="getStatistics",
     *     tags={"Statistics"},
     *     summary="Get personal statistics",
     *
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Start date for the statistics",
     *         example="2021-01-01T00:00:00.000Z"
     *     ),
     *     @OA\Parameter(
     *         name="until",
     *         in="query",
     *         description="End date for the statistics",
     *         example="2021-02-01T00:00:00.000Z"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="purpose",
     *                      description="The purpose of travel",
     *                      type="array",
     *
     *                      @OA\Items(
     *
     *                          @OA\Property(property="name", ref="#/components/schemas/Business"),
     *                          @OA\Property(property="count", type="integer", example=11),
     *                          @OA\Property(property="duration", type="integer", example=425, description="Duration in
     *                                                            minutes"),
     *                      )
     *                ),
     *                @OA\Property(
     *                    property="categories",
     *                    description="The categories of the travel",
     *                    type="array",
     *
     *                    @OA\Items(
     *
     *                        @OA\Property(property="name", ref="#/components/schemas/HafasTravelType"),
     *                        @OA\Property(property="count", type="integer", example=11),
     *                        @OA\Property(property="duration", type="integer", example=425, description="Duration in minutes"),
     *                    )
     *                ),
     *                @OA\Property(
     *                    property="operators",
     *                    description="The operators of the means of transport",
     *                    type="array",
     *
     *                    @OA\Items(
     *
     *                        @OA\Property(property="name", example="Gertruds Verkehrsgesellschaft mbH"),
     *                        @OA\Property(property="count", type="integer", example=10),
     *                        @OA\Property(property="duration", type="integer", example=424, description="Duration in minutes"),
     *                    )
     *                ),
     *                @OA\Property(
     *                    property="time",
     *                    description="Shows the daily travel volume",
     *                    type="array",
     *
     *                    @OA\Items(
     *
     *                        @OA\Property(property="date", type="string", example="2021-01-01T00:00:00.000Z"),
     *                        @OA\Property(property="count", type="integer", example=10),
     *                        @OA\Property(property="duration", type="integer", example=424, description="Duration in minutes"),
     *                    )
     *               ),
     *            )
     *        )
     *    ),
     *
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={
     *     {"passport": {"read-statistics"}}, {"token": {}}
     *
     *     }
     * )
     */
    public function getPersonalStatistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subWeeks(4);
        $until = isset($validated['until']) ? Carbon::parse($validated['until']) : Carbon::now();

        $purposes = StatisticsTravelPurposeResource::collection(
            StatisticBackend::getTravelPurposes(user: auth()->user(), from: $from, until: $until)
        );
        $categories = StatisticBackend::getTopTravelCategoryByUser(user: auth()->user(), from: $from, until: $until);
        $operators = StatisticBackend::getTopTripOperatorByUser(user: auth()->user(), from: $from, until: $until);
        $travelTime = StatisticBackend::getDailyTravelTimeByUser(user: auth()->user(), from: $from, until: $until);

        $returnData = [
            'purpose' => $purposes,
            'categories' => $categories,
            'operators' => $operators,
            'time' => $travelTime->map(function (Collection $row) {
                return [
                    'date' => $row->date->toDateString(),
                    'count' => $row->count,
                    'duration' => $row->duration,
                ];
            }),
        ];

        $additionalData = [
            'meta' => [
                'from' => $from,
                'until' => $until,
            ],
        ];

        return $this->sendResponse(data: $returnData, additional: $additionalData);
    }

    /**
     * @OA\Get(
     *      path="/statistics/daily/{date}",
     *      operationId="getDailyStatistics",
     *      tags={"Statistics"},
     *      summary="Get statistics and statuses of one day",
     *      description="Returns all statuses and statistics for the requested day",
     *
     *      @OA\Parameter(
     *          name="date",
     *          in="path",
     *          description="Date for the statistics in Format `YYYY-MM-DD`",
     *          example="2024-04-09",
     *          required=true,
     *
     *          @OA\Schema(type="string")
     *       ),
     *
     *      @OA\Parameter (
     *          name="timezone",
     *          in="query",
     *          description="Timezone for the date. If not set, the user's timezone will be used.",
     *          example="Europe/Berlin",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter (
     *          name="withPolylines",
     *          in="query",
     *          description="If this parameter is set, the polylines will be returned as well. Otherwise attribute is
     *          null.",
     *
     *          @OA\Schema(type="boolean")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property (
     *                  property="data",
     *                  type="object",
     *                  @OA\Property (
     *                      property="statuses", type="array",
     *
     *                      @OA\Items (
     *                          ref="#/components/schemas/StatusResource"
     *                      ),
     *                  ),
     *
     *                  @OA\Property (
     *                      property="polylines", type="array",
     *
     *                      @OA\Items (
     *                          ref="#/components/schemas/FeatureCollection"
     *                      ),
     *                  ),
     *
     *                  @OA\Property(
     *                      property="totalDistance",
     *                      example="74026",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="totalDuration",
     *                      example="4711",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="totalPoints",
     *                      example="42",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="prevDate",
     *                      example="2024-04-07",
     *                      type="string",
     *                      nullable=true,
     *                      description="Nearest earlier date with check-ins (YYYY-MM-DD), or null."
     *                  ),
     *                  @OA\Property(
     *                      property="nextDate",
     *                      example="2024-04-11",
     *                      type="string",
     *                      nullable=true,
     *                      description="Nearest later date with check-ins (YYYY-MM-DD), or null."
     *                  ),
     *              )
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=403, description="User not authorized to access this"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *       }
     *     )
     */
    public function getPersonalDailyStatistics(Request $request, string $dateString): JsonResponse
    {
        $validated = $request->validate([
            'withPolylines' => ['nullable', Rule::in(['true', 'false'])],
            'timezone' => [
                'nullable',
                'string',
                Rule::in(DateTimeZone::listIdentifiers()),
            ],
        ]);
        $date = Carbon::parse($dateString, $validated['timezone'] ?? auth()->user()->timezone);
        $statuses = DailyStatsController::getStatusesOnDate(auth()->user(), $date);

        $polylines = null;
        if (!empty($validated['withPolylines']) && $validated['withPolylines'] !== 'false') {
            $polylines = collect();
            $statuses->each(function (Status $status) use (&$polylines) {
                $polylines->add(new Feature(LocationController::forStatus($status)->getMapLines()));
            });
            $featureCollection = new FeatureCollection($polylines);
        }

        $prevDate = DailyStatsController::getPrevDateWithStatuses(auth()->user(), $date);
        $nextDate = DailyStatsController::getNextDateWithStatuses(auth()->user(), $date);

        return $this->sendResponse([
            'statuses' => StatusResource::collection($statuses),
            'polylines' => $polylines && count($polylines) ? $featureCollection : null,
            'totalDistance' => $statuses->sum('checkin.distance'),
            'totalDuration' => $statuses->sum('checkin.duration'),
            'totalPoints' => $statuses->sum('checkin.points'),
            'prevDate' => $prevDate?->format('Y-m-d'),
            'nextDate' => $nextDate?->format('Y-m-d'),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/statistics/global",
     *     operationId="getGlobalStatistics",
     *     tags={"Statistics"},
     *     summary="Get global statistics of the last 4 weeks",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/StatisticsGlobalData"
     *            ),
     *           @OA\Property(
     *               property="meta",
     *               type="object",
     *               @OA\Property(property="from", example="2021-01-01T00:00:00.000000Z"),
     *               @OA\Property(property="until", example="2021-02-01T00:00:00.000000Z"),
     *           ),
     *        ),
     *     ),
     *     security={
     *        {"passport": {"read-statistics"}}, {"token": {}}
     *     }
     *   )
     */
    public function getGlobalStatistics(): JsonResponse
    {
        $from = Carbon::now()->subWeeks(4);
        $until = Carbon::now();

        $globalStats = Cache::remember(
            key: CacheKey::getGlobalStatsKey($from, $until),
            ttl: config('trwl.cache.global-statistics-retention-seconds'), // 1 hour
            callback: static fn () => StatisticBackend::getGlobalCheckInStats($from, $until)
        );

        $additionalData = [
            'meta' => [
                'from' => $from,
                'until' => $until,
            ],
        ];

        return $this->sendResponse(data: new StatisticsGlobalData($globalStats), additional: $additionalData);
    }
}
