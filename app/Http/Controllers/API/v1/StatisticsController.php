<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\Export\ExportController;
use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
use App\Http\Resources\LeaderboardUserResource;
use App\Http\Resources\StatisticsGlobalData;
use App\Http\Resources\StatisticsTravelPurposeResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatisticsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/leaderboard",
     *      operationId="getLeaderboard",
     *      tags={"Leaderboard"},
     *      summary="[Auth optional] Get array of 20 best users",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUser"
     *                  )
     *              ),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function leaderboard(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard());
    }

    /**
     * @OA\Get(
     *      path="/leaderboard/distance",
     *      operationId="getLeaderboardByDistance",
     *      tags={"Leaderboard"},
     *      summary="[Auth optional] Get leaderboard array sorted by distance",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUser"
     *                  )
     *              ),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function leaderboardByDistance(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard(orderBy: 'distance'));
    }


    /**
     * @OA\Get(
     *      path="/leaderboard/friends",
     *      operationId="getLeaderboardByFriends",
     *      tags={"Leaderboard"},
     *      summary="Get friends-leaderboard array sorted",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUser"
     *                  )
     *              ),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function leaderboardFriends(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard(onlyFollowings: true));
    }

    /**
     * @OA\Get(
     *      path="/leaderboard/{month}",
     *      operationId="getMonthlyLeaderboard",
     *      tags={"Leaderboard"},
     *      summary="[Auth optional] Get leaderboard array for a specific month",
     *      @OA\Parameter(
     *          name="month",
     *          in="path",
     *          description="Month for the complete leaderboard in Format `YYYY-MM`",
     *          example="2022-04",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/LeaderboardUser"
     *                  )
     *              ),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No Event found for this id"),
     *       security={
     *           {"passport": {"read-statistics"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function leaderboardForMonth(string $date): AnonymousResourceCollection {
        $date = Carbon::parse($date);
        return LeaderboardUserResource::collection(LeaderboardBackend::getMonthlyLeaderboard(date: $date));
    }

    /**
     * @OA\Get(
     *     path="/statistics",
     *     operationId="getStatistics",
     *     tags={"Statistics"},
     *     summary="Get personal statistics",
     *     @OA\Property(
     *         property="from",
     *         description="Start date for the statistics",
     *         example="2021-01-01T00:00:00.000Z",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Property(
     *         property="until",
     *         description="End date for the statistics",
     *         example="2021-02-01T00:00:00.000Z",
     *         type="string",
     *         format="date-time"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="purpose",
     *                      description="The purpose of travel",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="name", ref="#/components/schemas/BusinessEnum"),
     *                          @OA\Property(property="count", type="integer", example=11),
     *                          @OA\Property(property="duration", type="integer", example=425, description="Duration in
     *                                                            minutes"),
     *                      )
     *                ),
     *                @OA\Property(
     *                    property="categories",
     *                    description="The categories of the travel",
     *                    type="array",
     *                    @OA\Items(
     *                        @OA\Property(property="name", ref="#/components/schemas/TrainCategoryEnum"),
     *                        @OA\Property(property="count", type="integer", example=11),
     *                        @OA\Property(property="duration", type="integer", example=425, description="Duration in
     *                                                          minutes"),
     *                    )
     *                ),
     *                @OA\Property(
     *                    property="operators",
     *                    description="The operators of the means of transport",
     *                    type="array",
     *                    @OA\Items(
     *                        @OA\Property(property="name", example="Gertruds Verkehrsgesellschaft mbH"),
     *                        @OA\Property(property="count", type="integer", example=10),
     *                        @OA\Property(property="duration", type="integer", example=424, description="Duration in
     *                                                          minutes"),
     *                    )
     *                ),
     *                @OA\Property(
     *                    property="time",
     *                    description="Shows the daily travel volume",
     *                    type="array",
     *                    @OA\Items(
     *                        @OA\Property(property="date", type="string", example="2021-01-01T00:00:00.000Z"),
     *                        @OA\Property(property="count", type="integer", example=10),
     *                        @OA\Property(property="duration", type="integer", example=424, description="Duration in
     *                                                          minutes"),
     *                    )
     *               ),
     *            ),
     *            @OA\Property(
     *                property="meta",
     *                type="object",
     *                @OA\Property(property="from", example="2021-01-01T00:00:00.000000Z"),
     *                @OA\Property(property="until", example="2021-02-01T00:00:00.000000Z"),
     *            )
     *        )
     *    ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={
     *     {"passport": {"read-statistics"}}, {"token": {}}
     *
     *     }
     * )
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
            'time'       => $travelTime->map(function(Collection $row) {
                return [
                    'date'     => $row->date->toDateString(),
                    'count'    => $row->count,
                    'duration' => $row->duration,
                ];
            })
        ];

        $additionalData = [
            'meta' => [
                'from'  => $from,
                'until' => $until
            ]
        ];

        return $this->sendResponse(data: $returnData, additional: $additionalData);
    }

    /**
     * @OA\Get(
     *     path="/statistics/global",
     *     operationId="getGlobalStatistics",
     *     tags={"Statistics"},
     *     summary="Get global statistics of the last 4 weeks",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="distance",
     *                     description="Globally travelled distance in meters",
     *                     type="integer",
     *                     example=1000
     *                 ),
     *                 @OA\Property(
     *                     property="duration",
     *                     description="Globally travelled duration in minutes",
     *                     type="integer",
     *                     example=1000
     *                 ),
     *                 @OA\Property(
     *                     property="activeUsers",
     *                     description="Number of active users",
     *                     type="integer",
     *                     example=1000
     *                ),
     *           @OA\Property(
     *               property="meta",
     *               type="object",
     *               @OA\Property(property="from", example="2021-01-01T00:00:00.000000Z"),
     *               @OA\Property(property="until", example="2021-02-01T00:00:00.000000Z"),
     *           ),
     *        )
     *      ),
     *     ),
     *     security={
     *        {"passport": {"read-statistics"}}, {"token": {}}
     *
     *     }
     *     )
     *
     *
     * @return JsonResponse
     */
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

        return $this->sendResponse(data: $data, additional: $additionalData);
    }

    public function generateTravelExport(Request $request): JsonResponse|StreamedResponse|Response {
        $validated = $request->validate([
                                            'from'     => ['required', 'date', 'before_or_equal:until'],
                                            'until'    => ['required', 'date', 'after_or_equal:from'],
                                            'filetype' => ['required', Rule::in(['json', 'csv', 'pdf'])],
                                        ]);

        return ExportController::generateExport(
            from:     Carbon::parse($validated['from']),
            until:    Carbon::parse($validated['until']),
            filetype: $request->input('filetype')
        );
    }
}
