<?php

namespace App\Http\Controllers\API\v1;

use App\Dto\GeoJson\Feature;
use App\Dto\GeoJson\FeatureCollection;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\StatusResource;
use App\Http\Resources\StopoverResource;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\Ticket;
use App\Models\Trip;
use App\Services\Checkin\CheckinService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StatusUpdateBody',
    title: 'StatusUpdateBody',
    description: 'Status Update Body',
    properties: [
        new OA\Property(property: 'body', description: 'Status-Text to be displayed alongside the checkin', type: 'string', example: 'Wow. This train is extremely crowded!', nullable: true, maxLength: 280),
        new OA\Property(property: 'business', ref: '#/components/schemas/Business'),
        new OA\Property(property: 'visibility', ref: '#/components/schemas/StatusVisibility'),
        new OA\Property(property: 'eventId', description: 'The ID of the event this status is related to - or null', type: 'string', example: '1', nullable: true),
        new OA\Property(property: 'manualDeparture', description: 'Manual departure time set by the user', type: 'string', format: 'date', example: '2020-01-01 12:00:00', nullable: true),
        new OA\Property(property: 'manualArrival', description: 'Manual arrival time set by the user', type: 'string', format: 'date', example: '2020-01-01 13:00:00', nullable: true),
        new OA\Property(property: 'destinationId', description: 'Destination station id', type: 'string', example: '1', nullable: true),
        new OA\Property(property: 'destinationArrivalPlanned', description: 'Destination arrival time', type: 'string', format: 'date', example: '2020-01-01 13:00:00', nullable: true),
    ],
)]
#[OA\Schema(
    schema: 'StatusAssignTicketBody',
    title: 'StatusAssignTicketBody',
    description: 'Assign or remove a ticket from a status',
    required: ['ticketId'],
    properties: [
        new OA\Property(property: 'ticketId', description: 'UUID of the ticket to assign, or null to remove the assignment', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000', nullable: true),
    ],
)]
#[OA\Schema(
    schema: 'Polyline',
    title: 'Polyline',
    description: 'Polyline of a single status as GeoJSON Feature',
    required: ['type', 'geometry', 'properties'],
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'Feature'),
        new OA\Property(
            property: 'geometry',
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'LineString'),
                new OA\Property(property: 'coordinates', type: 'array', items: new OA\Items(example: '[[8.39767,49.01625],[8.45947,49.06576]]')),
            ],
            type: 'object',
        ),
        new OA\Property(
            property: 'properties',
            properties: [
                new OA\Property(property: 'statusId', type: 'integer', example: 1337),
            ],
            type: 'object',
        ),
    ],
)]
class StatusController extends Controller
{
    #[OA\Get(
        path: '/dashboard',
        operationId: 'getDashboard',
        description: 'Returns paginated statuses of personal dashboard',
        summary: 'Get paginated statuses of personal dashboard',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Dashboard'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'links', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/PaginationMeta',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Not logged in'),
        ],
    )]
    public static function getDashboard(): AnonymousResourceCollection
    {
        return StatusResource::collection(DashboardController::getPrivateDashboard(Auth::user()));
    }

    #[OA\Get(
        path: '/dashboard/future',
        operationId: 'getFutureDashboard',
        description: 'Returns paginated statuses of the authenticated user, that are more than 20 minutes in the future',
        summary: 'Get paginated future statuses of current user',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Dashboard'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page of pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data', 'links', 'meta'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/Links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Not logged in'),
        ],
    )]
    public static function getFutureCheckins(): AnonymousResourceCollection
    {
        return StatusResource::collection(StatusBackend::getFutureCheckins());
    }

    #[OA\Schema(
        schema: 'DuplicateCheckinGroup',
        title: 'DuplicateCheckinGroup',
        description: 'A group of check-ins with the same trip and origin stopover (duplicates)',
        required: ['statuses'],
        properties: [
            new OA\Property(
                property: 'statuses',
                type: 'array',
                items: new OA\Items(ref: StatusResource::class),
            ),
        ],
    )]
    #[OA\Get(
        path: '/statuses/duplicates',
        operationId: 'getDuplicateCheckins',
        description: 'Temporary cleanup endpoint: returns groups of check-ins the authenticated user has checked in more than once for the same trip and origin stopover. Will be removed after 2026-05-31.',
        summary: '[Deprecated] Get duplicate check-ins of the authenticated user',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Status'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/DuplicateCheckinGroup'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Not logged in'),
        ],
        deprecated: true,
    )]
    public function getDuplicateCheckins(): JsonResponse
    {
        // TODO: remove endpoint after 2026-05-31
        $userId = Auth::id();

        $duplicatePairs = DB::table('train_checkins')
            ->select('trip_id', 'origin_stopover_id')
            ->where('user_id', $userId)
            ->whereNotNull('origin_stopover_id')
            ->groupBy('trip_id', 'origin_stopover_id')
            ->havingRaw('COUNT(*) > 1');

        $checkinRows = DB::table('train_checkins')
            ->joinSub($duplicatePairs, 'dup_pairs', function ($join): void {
                $join->on('train_checkins.trip_id', '=', 'dup_pairs.trip_id')
                    ->on('train_checkins.origin_stopover_id', '=', 'dup_pairs.origin_stopover_id');
            })
            ->where('train_checkins.user_id', $userId)
            ->select('train_checkins.status_id', 'train_checkins.trip_id', 'train_checkins.origin_stopover_id')
            ->orderBy('train_checkins.trip_id')
            ->orderBy('train_checkins.origin_stopover_id')
            ->orderBy('train_checkins.id')
            ->get();

        if ($checkinRows->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $statuses = Status::with([
            'event',
            'likes',
            'user',
            'createdByUser',
            'checkin.originStopover.station',
            'checkin.destinationStopover.station',
            'checkin.trip.operator',
            'checkin.trip.motisSourceLicense',
            'checkin.statusTags',
            'tags',
            'mentions.mentioned',
            'ticket',
            'client',
        ])
            ->whereIn('id', $checkinRows->pluck('status_id'))
            ->get()
            ->keyBy('id');

        $groups = $checkinRows
            ->groupBy(fn ($row) => $row->trip_id . ':' . $row->origin_stopover_id)
            ->map(fn ($rows) => [
                'statuses' => $rows
                    ->map(fn ($row) => new StatusResource($statuses->get($row->status_id)))
                    ->filter()
                    ->values(),
            ])
            ->values();

        return response()->json(['data' => $groups]);
    }

    #[OA\Get(
        path: '/statuses',
        operationId: 'getActiveStatuses',
        description: 'Returns all currently active statuses that are visible to the (un)authenticated user',
        summary: '[Auth optional] Get active statuses',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Status'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
        ],
    )]
    public function enRoute(): AnonymousResourceCollection
    {
        return StatusResource::collection(StatusBackend::getActiveStatuses());
    }

    #[OA\Get(
        path: '/positions',
        operationId: 'getLivePositionsForActiveStatuses',
        description: 'Returns an array of live position objects for active statuses',
        summary: '[Auth optional] get live positions for active statuses',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Status'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LivePointDto'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
        ],
    )]
    public function livePositions(): JsonResource
    {
        return JsonResource::collection(StatusBackend::getLivePositions());
    }

    #[OA\Get(
        path: '/positions/{ids}',
        operationId: 'getLivePositionsForStatuses',
        description: 'Returns an array of live position objects for given status IDs',
        summary: '[Auth optional] get live positions for given statuses',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Status'],
        parameters: [
            new OA\Parameter(
                name: 'ids',
                description: 'Status-IDs separated by comma',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: '1337,1338',
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LivePointDto'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
        ],
    )]
    public function getLivePositionForStatus($ids): AnonymousResourceCollection
    {
        return JsonResource::collection(StatusBackend::getLivePositionForStatus($ids));
    }

    #[OA\Get(
        path: '/status',
        operationId: 'listStatuses',
        description: 'Returns cursor-paginated statuses filtered by given parameters. The departure window (from..to) defaults to the last 7 days and must not exceed 365 days.',
        summary: '[Auth optional] List and filter statuses',
        tags: ['Status'],
        parameters: [
            new OA\Parameter(
                name: 'body',
                description: 'Filter by text in status body',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                example: 'Having a great trip!',
            ),
            new OA\Parameter(
                name: 'user_id',
                description: 'Filter by user ID',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
                example: 42,
            ),
            new OA\Parameter(
                name: 'from',
                description: 'Lower bound for departure (date, e.g. 2024-01-01). Defaults to 7 days before "to".',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date'),
                example: '2024-01-01',
            ),
            new OA\Parameter(
                name: 'to',
                description: 'Upper bound for departure (date, e.g. 2024-01-31). Defaults to now+20min. Range from..to must not exceed 365 days.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date'),
                example: '2024-01-31',
            ),
            new OA\Parameter(
                name: 'origin_text',
                description: 'Filter by origin station name',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                example: 'Central Station',
            ),
            new OA\Parameter(
                name: 'origin_id',
                description: 'Filter by origin station ID',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
                example: 5,
            ),
            new OA\Parameter(
                name: 'destination_text',
                description: 'Filter by destination station name',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                example: 'Main Square',
            ),
            new OA\Parameter(
                name: 'destination_id',
                description: 'Filter by destination station ID',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
                example: 10,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'list of matching statuses',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function list(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:32'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'origin_text' => ['nullable', 'string', 'max:64'],
            'origin_id' => ['nullable', 'integer', 'exists:train_stations,id'],
            'destination_text' => ['nullable', 'string', 'max:64'],
            'destination_id' => ['nullable', 'integer', 'exists:train_stations,id'],
        ]);

        $user = auth()->user();
        $isOwnSearch = isset($validated['user_id']) && (int) $validated['user_id'] === $user->id;

        if ($isOwnSearch) {
            // own search ignores day limit (=> less checkins to search)
            $to = isset($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;
            $from = isset($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;

            if ($from !== null && $to !== null && $from->isAfter($to)) {
                throw ValidationException::withMessages(['from' => [__('errors.date-range-order')]]);
            }
        } else {
            $to = isset($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : now()->addMinutes(20);
            $from = isset($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : $to->copy()->subDays(7);

            if ($from->isAfter($to)) {
                throw ValidationException::withMessages(['from' => [__('errors.date-range-order')]]);
            }
            if ($from->diffInDays($to) > 365) {
                throw ValidationException::withMessages(['from' => [__('errors.date-range-max')]]);
            }
        }

        $query = Status::query()->orderByDesc('train_checkins.departure');

        if (isset($validated['body'])) {
            $query->where('body', 'like', '%' . $validated['body'] . '%');
        }

        $hasOriginFilter = isset($validated['origin_text']) || isset($validated['origin_id']);
        $hasDestinationFilter = isset($validated['destination_text']) || isset($validated['destination_id']);

        $checkinJoin = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)
            ? DB::raw('`train_checkins` FORCE INDEX (idx_tc_departure_status)')
            : 'train_checkins'; // force best index in mysql and mariadb, but fall back here for sqlite / tests

        $query->join($checkinJoin, 'train_checkins.status_id', '=', 'statuses.id')
            ->join('users', 'statuses.user_id', '=', 'users.id')
            ->when($hasOriginFilter, fn ($q) => $q
                ->join('train_stopovers as origin_stopover', 'train_checkins.origin_stopover_id', '=', 'origin_stopover.id')
                ->join('train_stations as origin_station', 'origin_stopover.train_station_id', '=', 'origin_station.id')
            )
            ->when($hasDestinationFilter, fn ($q) => $q
                ->join('train_stopovers as destination_stopover', 'train_checkins.destination_stopover_id', '=', 'destination_stopover.id')
                ->join('train_stations as destination_station', 'destination_stopover.train_station_id', '=', 'destination_station.id')
            )
            ->when(isset($validated['origin_text']), fn ($q) => $q
                ->where('origin_station.name', 'like', '%' . $validated['origin_text'] . '%')
            )
            ->when(isset($validated['origin_id']), fn ($q) => $q
                ->where('origin_station.id', $validated['origin_id'])
            )
            ->when(isset($validated['destination_text']), fn ($q) => $q
                ->where('destination_station.name', 'like', '%' . $validated['destination_text'] . '%')
            )
            ->when(isset($validated['destination_id']), fn ($q) => $q
                ->where('destination_station.id', $validated['destination_id'])
            )
            ->when(isset($validated['user_id']), fn ($q) => $q
                ->where('users.id', $validated['user_id'])
            )
            ->where(\App\Http\Controllers\Backend\Transport\StatusController::filterStatusVisibility($user))
            ->when($from !== null, fn ($q) => $q->where('train_checkins.departure', '>=', $from))
            ->when($to !== null, fn ($q) => $q->where('train_checkins.departure', '<=', $to))
            ->whereNotIn('statuses.user_id', $user->mutedUsers()->select('muted_id'))
            ->whereNotIn('statuses.user_id', $user->blockedUsers()->select('blocked_id'))
            ->whereNotIn('statuses.user_id', $user->blockedByUsers()->select('user_id'))
            ->select('statuses.*');

        return StatusResource::collection($query->cursorPaginate(20));
    }

    #[OA\Get(
        path: '/status/{id}',
        operationId: 'getSingleStatus',
        description: 'Returns a single status Object, if user is authorized to see it',
        summary: '[Auth optional] Get single statuses',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Status'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Status-ID',
                in: 'path',
                schema: new OA\Schema(type: 'integer'),
                example: 1337,
            ),
            new OA\Parameter(
                name: 'withIdentifiers',
                description: 'Include station identifiers in origin and destination stopovers',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean'),
                example: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/StatusResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
        ],
    )]
    public function show(Request $request, int $id): StatusResource|JsonResponse
    {
        $status = StatusBackend::getStatus($id);
        try {
            $this->authorize('view', $status);
        } catch (AuthorizationException) {
            return response()->json(['message' => 'Status invisible to you.'], 403);
        }

        if ($request->boolean('withIdentifiers')) {
            $status->checkin->originStopover->station->load('stationIdentifiers');
            $status->checkin->destinationStopover->station->load('stationIdentifiers');
        }

        return new StatusResource($status);
    }

    #[OA\Delete(
        path: '/status/{id}',
        operationId: 'destroySingleStatus',
        description: 'Deletes a single status Object, if user is authorized to',
        summary: 'Destroy a status',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        tags: ['Status'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Status-ID',
                in: 'path',
                schema: new OA\Schema(type: 'integer'),
                example: 1337,
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Status deleted.'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(
                response: 403,
                description: 'User not authorized to manipulate this status',
            ),
        ],
    )]
    public function destroy(int $statusId): JsonResponse
    {
        try {
            StatusBackend::DeleteStatus(Auth::user(), $statusId);

            return response()->json(null, 204);
        } catch (AuthorizationException) {
            return $this->sendError('You are not allowed to delete this status.', 403);
        } catch (ModelNotFoundException) {
            return $this->sendError('No status found for this id.');
        }
    }

    /**
     * @throws ValidationException
     */
    #[OA\Put(
        path: '/status/{id}',
        operationId: 'updateSingleStatus',
        description: 'Updates a single status Object, if user is authorized to',
        summary: 'Update a status',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StatusUpdateBody'),
        ),
        tags: ['Status'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Status-ID',
                in: 'path',
                schema: new OA\Schema(type: 'integer'),
                example: 1337,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/StatusResource')],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(
                response: 403,
                description: 'User not authorized to manipulate this status',
            ),
        ],
    )]
    public function update(Request $request, int $statusId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Just changing of metadata
            'body' => ['nullable', 'max:280', 'nullable'],
            'business' => [new Enum(Business::class)],
            'visibility' => [new Enum(StatusVisibility::class)],
            'eventId' => ['nullable', 'integer', 'exists:events,id'],

            // Changing of Checkin-Metadata
            'manualDeparture' => ['nullable', 'date'],
            'manualArrival' => ['nullable', 'date'],

            // Following attributes are needed, if user want's to change the destination
            'destinationId' => ['required_with:destinationArrivalPlanned', 'exists:train_stations,id'],
            'destinationArrivalPlanned' => ['required_with:destinationId', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $validated = $validator->validate();

        try {
            $status = Status::findOrFail($statusId);
            $this->authorize('update', $status);

            // Check for disallowed status visibility changes
            if (auth()->user()->can('disallow-status-visibility-change') && $validated['visibility'] !== StatusVisibility::PRIVATE->value) {
                return $this->sendError('You are not allowed to change the visibility to anything else than private', 403);
            }

            DB::beginTransaction();
            if (
                isset($validated['destinationId'], $validated['destinationArrivalPlanned'])
                && (
                    ((int) $validated['destinationId']) !== $status->checkin->destinationStopover->station->id
                    || (Carbon::parse($validated['destinationArrivalPlanned'])->ne($status->checkin->destinationStopover->arrival_planned))
                )
            ) {
                $arrival = Carbon::parse($validated['destinationArrivalPlanned'])->timezone(config('app.timezone'));
                $stopover = Stopover::where('train_station_id', $validated['destinationId'])
                    ->where('arrival_planned', $arrival)
                    ->where('trip_id', $status->checkin->trip_id)
                    ->first();

                if ($stopover === null) {
                    return $this->sendError('Invalid stopover given', 400);
                }

                app(CheckinService::class)->changeDestination(
                    checkin: $status->checkin,
                    newDestinationStopover: $stopover,
                );
            }
            $updatePayload = [];
            if (array_key_exists('body', $validated)) {
                $updatePayload['body'] = $validated['body'] ?? null;
            }
            if (array_key_exists('business', $validated)) {
                $updatePayload['business'] = Business::from($validated['business']);
            }

            if (!$status->lock_visibility && array_key_exists('visibility', $validated)) {
                // If moderation has locked the visibility, prevent the user from changing it
                $updatePayload['visibility'] = StatusVisibility::from($validated['visibility']);
            }

            if (array_key_exists('eventId', $validated)) { // don't use isset here as it would return false if eventId is null
                $updatePayload['event_id'] = $validated['eventId'];
            }

            $status->update($updatePayload);

            if (array_key_exists('manualDeparture', $validated)) {
                $manualDeparture = isset($validated['manualDeparture'])
                    ? Carbon::parse($validated['manualDeparture'], auth()->user()->timezone)->setSecond(0)->setMillisecond(0)
                    : null;
                $status->checkin->update(['manual_departure' => $manualDeparture]);
            }
            if (array_key_exists('manualArrival', $validated)) {
                $manualArrival = isset($validated['manualArrival'])
                    ? Carbon::parse($validated['manualArrival'], auth()->user()->timezone)->setSecond(0)->setMillisecond(0)
                    : null;
                $status->checkin->update(['manual_arrival' => $manualArrival]);
            }

            // check duration of manual arrival and departure
            $arrivalDelayInHours = 0;
            $departureDelayInHours = 0;
            if (!empty($manualDeparture)) {
                $departureDelayInHours = abs($manualDeparture->diffInHours($status->checkin->departure));
            }

            if (!empty($manualArrival)) {
                $arrivalDelayInHours = abs($manualArrival->diffInHours($status->checkin->arrival));
            }

            if ($departureDelayInHours > config('trwl.max_delay_hours') || $arrivalDelayInHours > config('trwl.max_delay_hours')) {
                DB::rollBack();

                return $this->sendError('The delay of the manual arrival or departure is too high.', 400);
            }

            DB::commit();
            $status = $status->fresh();
            StatusUpdateEvent::dispatch($status);

            return $this->sendResponse(new StatusResource($status));
        } catch (ModelNotFoundException) {
            DB::rollBack();

            return $this->sendError('Status not found');
        } catch (AuthorizationException) {
            DB::rollBack();

            return $this->sendError('You are not authorized to edit this status', 403);
        } catch (InvalidArgumentException) {
            DB::rollBack();

            return $this->sendError('Invalid Arguments', 400);
        }
    }

    #[OA\Put(
        path: '/statuses/{id}/tickets',
        operationId: 'assignTicketToStatus',
        description: 'Assign or remove a ticket from a status. Only the status owner can perform this action.',
        summary: 'Assign or remove a ticket from a status',
        security: [['passport' => ['write-statuses']], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StatusAssignTicketBody'),
        ),
        tags: ['Tickets'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Status-ID',
                in: 'path',
                schema: new OA\Schema(type: 'integer'),
                example: 1337,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/StatusResource')],
                ),
            ),
            new OA\Response(response: 404, description: 'Status or ticket not found'),
        ],
    )]
    public function assignTicket(Request $request, int $id): JsonResponse
    {
        $status = Status::find($id);
        if ($status === null || $status->user_id !== auth()->id()) {
            return $this->sendError('Status not found.', 404);
        }

        $validated = Validator::make($request->all(), [
            'ticketId' => ['present', 'nullable', 'uuid'],
        ])->validate();

        if ($validated['ticketId'] !== null) {
            $ticket = Ticket::where('id', $validated['ticketId'])
                ->where('user_id', auth()->id())
                ->first();

            if ($ticket === null) {
                return $this->sendError('Ticket not found.', 404);
            }

            $status->ticket_id = $ticket->id;
        } else {
            $status->ticket_id = null;
        }

        $status->save();

        return $this->sendResponse(new StatusResource($status->fresh()));
    }

    /**
     * @todo extract this to backend
     * @todo does this conform to the private checkin-shit?
     */
    #[OA\Get(
        path: '/polyline/{ids}',
        operationId: 'getPolylines',
        description: 'Returns GeoJSON for all requested status IDs',
        summary: '[Auth optional] Get GeoJSON for statuses',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Status'],
        parameters: [
            new OA\Parameter(
                name: 'ids',
                description: 'comma seperated status IDs',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: '1337,1338',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'type', example: 'FeatureCollection'),
                                new OA\Property(
                                    property: 'features',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/Polyline'),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
        ],
    )]
    public function getPolyline(string $parameters): JsonResource
    {
        $ids = explode(',', $parameters, 50);
        $geoJsonFeatures = Status::whereIn('id', $ids)
            ->with('checkin.Trip.polyline')
            ->get()
            ->filter(function (Status $status) {
                try {
                    $this->authorize('view', $status);
                } catch (AuthorizationException) {
                    return false;
                }

                return true;
            })
            ->map(function ($status) {
                return new Feature(
                    LocationController::forStatus($status)->getMapLines(),
                    'LineString',
                    $status->id
                );
            });
        $geoJson = new FeatureCollection($geoJsonFeatures);

        return $ids ? new JsonResource($geoJson) : $this->sendError('');
    }

    #[OA\Get(
        path: '/stopovers/{ids}',
        operationId: 'getStopOvers',
        description: 'Returns all underway-stops for stations',
        summary: '[Auth optional] Get stopovers for statuses',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Status'],
        parameters: [
            new OA\Parameter(
                name: 'ids',
                description: 'comma seperated trip IDs',
                in: 'path',
                schema: new OA\Schema(type: 'string'),
                example: '1,2',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: '1',
                                    description: 'Array of stopovers. Key describes trip id',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/StopoverResource'),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'No status found for this id'),
            new OA\Response(response: 403, description: 'User not authorized to access this status'),
        ],
    )]
    public function getStopovers(string $parameters): JsonResponse
    {
        $tripIds = array_unique(explode(',', $parameters, 50));
        $trips = Trip::with('stopovers.station')->whereIn('id', $tripIds)->get()->mapWithKeys(function ($trip) {
            return [$trip->id => StopoverResource::collection($trip->stopovers)];
        });

        return $this->sendResponse($trips);
    }

    #[OA\Get(
        path: '/user/statuses/active',
        operationId: 'userState',
        description: 'This request returns whether the currently logged-in user has an active check-in or not.',
        summary: 'User state',
        security: [['passport' => ['read-statuses']], ['token' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/StatusResource',
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 204, description: 'No active checkin'),
        ],
    )]
    public function getActiveStatus(): StatusResource|JsonResponse
    {
        $latestStatuses = UserBackend::statusesForUser(Auth::user());
        if ($latestStatuses->count() > 0) {
            foreach ($latestStatuses as $status) {
                if ($status->checkin->originStopover->departure->isPast()
                    && $status->checkin->destinationStopover->arrival->isFuture()) {
                    return new StatusResource($status);
                }
            }
        }

        return response()->json(null, 204);
    }
}
