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
use OpenApi\Attributes as OA;

class StatisticsController extends Controller
{
    private static string $cacheRetentionConfigKey = 'trwl.cache.leaderboard-retention-seconds';

    private LeaderboardBackend $leaderboardBackend;

    public function __construct(LeaderboardBackend $leaderboard)
    {
        $this->leaderboardBackend = $leaderboard;
    }

    #[OA\Get(
        path: '/leaderboard',
        operationId: 'getLeaderboard',
        summary: '[Auth optional] Get array of 20 best users',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Leaderboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LeaderboardUserResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No Event found for this id'),
        ],
    )]
    public function leaderboard(): AnonymousResourceCollection
    {
        return LeaderboardUserResource::collection($this->leaderboardBackend->getCachedGlobalLeaderboard());
    }

    #[OA\Get(
        path: '/leaderboard/distance',
        operationId: 'getLeaderboardByDistance',
        summary: '[Auth optional] Get leaderboard array sorted by distance',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Leaderboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LeaderboardUserResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No Event found for this id'),
        ],
    )]
    public function leaderboardByDistance(): AnonymousResourceCollection
    {
        return LeaderboardUserResource::collection($this->leaderboardBackend->getCachedDistanceLeaderboard());
    }

    #[OA\Get(
        path: '/leaderboard/friends',
        operationId: 'getLeaderboardByFriends',
        summary: 'Get friends-leaderboard array sorted',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Leaderboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LeaderboardUserResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No Event found for this id'),
        ],
    )]
    public function leaderboardFriends(): AnonymousResourceCollection
    {
        return LeaderboardUserResource::collection($this->leaderboardBackend->getCachedFriendsLeaderboard());
    }

    #[OA\Get(
        path: '/leaderboard/{month}',
        operationId: 'getMonthlyLeaderboard',
        summary: '[Auth optional] Get leaderboard array for a specific month',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Leaderboard'],
        parameters: [
            new OA\Parameter(
                name: 'month',
                description: 'Month for the complete leaderboard in Format `YYYY-MM`',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: '2022-04',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LeaderboardUserResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No Event found for this id'),
        ],
    )]
    public function leaderboardForMonth(string $date): AnonymousResourceCollection
    {
        try {
            $date = Carbon::parse($date);
        } catch (\Exception) {
            abort(400);
        }

        return LeaderboardUserResource::collection($this->leaderboardBackend->getCachedMonthlyLeaderboard($date));
    }

    #[OA\Get(
        path: '/statistics',
        operationId: 'getStatistics',
        summary: 'Get personal statistics',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'from',
                description: 'Start date for the statistics',
                in: 'query',
                example: '2021-01-01T00:00:00.000Z',
            ),
            new OA\Parameter(
                name: 'until',
                description: 'End date for the statistics',
                in: 'query',
                example: '2021-02-01T00:00:00.000Z',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'purpose',
                                    description: 'The purpose of travel',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(
                                                property: 'name',
                                                ref: '#/components/schemas/Business',
                                            ),
                                            new OA\Property(
                                                property: 'count',
                                                type: 'integer',
                                                example: 11,
                                            ),
                                            new OA\Property(
                                                property: 'duration',
                                                description: 'Duration in minutes',
                                                type: 'integer',
                                                example: 425,
                                            ),
                                        ],
                                    ),
                                ),
                                new OA\Property(
                                    property: 'categories',
                                    description: 'The categories of the travel',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(
                                                property: 'name',
                                                ref: '#/components/schemas/HafasTravelType',
                                            ),
                                            new OA\Property(
                                                property: 'count',
                                                type: 'integer',
                                                example: 11,
                                            ),
                                            new OA\Property(
                                                property: 'duration',
                                                description: 'Duration in minutes',
                                                type: 'integer',
                                                example: 425,
                                            ),
                                        ],
                                    ),
                                ),
                                new OA\Property(
                                    property: 'operators',
                                    description: 'The operators of the means of transport',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(
                                                property: 'name',
                                                example: 'Gertruds Verkehrsgesellschaft mbH',
                                            ),
                                            new OA\Property(
                                                property: 'count',
                                                type: 'integer',
                                                example: 10,
                                            ),
                                            new OA\Property(
                                                property: 'duration',
                                                description: 'Duration in minutes',
                                                type: 'integer',
                                                example: 424,
                                            ),
                                        ],
                                    ),
                                ),
                                new OA\Property(
                                    property: 'time',
                                    description: 'Shows the daily travel volume',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(
                                                property: 'date',
                                                type: 'string',
                                                example: '2021-01-01T00:00:00.000Z',
                                            ),
                                            new OA\Property(
                                                property: 'count',
                                                type: 'integer',
                                                example: 10,
                                            ),
                                            new OA\Property(
                                                property: 'duration',
                                                description: 'Duration in minutes',
                                                type: 'integer',
                                                example: 424,
                                            ),
                                        ],
                                    ),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ],
    )]
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

    #[OA\Get(
        path: '/statistics/daily/{date}',
        operationId: 'getDailyStatistics',
        description: 'Returns all statuses and statistics for the requested day',
        summary: 'Get statistics and statuses of one day',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Statistics'],
        parameters: [
            new OA\Parameter(
                name: 'date',
                description: 'Date for the statistics in Format `YYYY-MM-DD`',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: '2024-04-09',
            ),
            new OA\Parameter(
                name: 'timezone',
                description: 'Timezone for the date. If not set, the user\'s timezone will be used.',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                example: 'Europe/Berlin',
            ),
            new OA\Parameter(
                name: 'withPolylines',
                description: 'If this parameter is set, the polylines will be returned as well. Otherwise attribute is null.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'statuses',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                                ),
                                new OA\Property(
                                    property: 'polylines',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/FeatureCollection'),
                                ),
                                new OA\Property(
                                    property: 'totalDistance',
                                    type: 'integer',
                                    example: '74026',
                                ),
                                new OA\Property(
                                    property: 'totalDuration',
                                    type: 'integer',
                                    example: '4711',
                                ),
                                new OA\Property(
                                    property: 'totalPoints',
                                    type: 'integer',
                                    example: '42',
                                ),
                                new OA\Property(
                                    property: 'prevDate',
                                    description: 'Nearest earlier date with check-ins (YYYY-MM-DD), or null.',
                                    type: 'string',
                                    example: '2024-04-07',
                                    nullable: true,
                                ),
                                new OA\Property(
                                    property: 'nextDate',
                                    description: 'Nearest later date with check-ins (YYYY-MM-DD), or null.',
                                    type: 'string',
                                    example: '2024-04-11',
                                    nullable: true,
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'User not authorized to access this'),
        ],
    )]
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

    #[OA\Get(
        path: '/statistics/global',
        operationId: 'getGlobalStatistics',
        summary: 'Get global statistics of the last 4 weeks',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Statistics'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/StatisticsGlobalData',
                            type: 'object',
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(
                                    property: 'from',
                                    example: '2021-01-01T00:00:00.000000Z',
                                ),
                                new OA\Property(
                                    property: 'until',
                                    example: '2021-02-01T00:00:00.000000Z',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
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
