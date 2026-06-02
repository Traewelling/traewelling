<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Dto\TicketStatisticsDto;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketStatisticsResource;
use App\Models\Ticket;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class TicketController extends Controller
{
    #[OA\Get(
        path: '/tickets',
        operationId: 'getTickets',
        description: 'Returns all tickets of the currently authenticated user. Only available to users with the closed-beta role. Optionally filter by validity date using the `validOn` parameter.',
        summary: 'List all tickets of the current user',
        security: [['passport' => []], ['token' => []]],
        tags: ['Tickets'],
        parameters: [
            new OA\Parameter(
                name: 'validOn',
                description: 'Only return tickets valid on this date (YYYY-MM-DD). A ticket is valid if its valid_from is on or before this date (or null) and its valid_until is on or after this date (or null).',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-01-15'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TicketResource'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
        ],
    )]
    private static function loadStats(Collection $tickets): void
    {
        if ($tickets->isEmpty()) {
            return;
        }

        $ids = $tickets->pluck('id');

        $stats = DB::table('statuses')
            ->leftJoin('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
            ->whereIn('statuses.ticket_id', $ids)
            ->groupBy('statuses.ticket_id')
            ->selectRaw('statuses.ticket_id, COUNT(train_checkins.id) AS trip_count, COALESCE(SUM(train_checkins.distance), 0) AS total_distance, COALESCE(SUM(train_checkins.duration), 0) AS total_duration')
            ->get()
            ->keyBy('ticket_id');

        $tickets->each(function (Ticket $ticket) use ($stats): void {
            $s = $stats->get($ticket->id);
            $ticket->trip_count = (int) ($s?->trip_count ?? 0);
            $ticket->total_distance = (int) ($s?->total_distance ?? 0);
            $ticket->total_duration = (int) ($s?->total_duration ?? 0);
        });
    }

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $this->authorize('viewAny', Ticket::class);
        } catch (AuthorizationException) {
            return $this->sendError('This feature is not available for your account.', 403);
        }

        $query = Ticket::where('user_id', auth()->id());

        if ($request->filled('validOn')) {
            $date = Carbon::parse($request->input('validOn'))->startOfDay();
            $query->where(static function ($q) use ($date): void {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $date);
            })->where(static function ($q) use ($date): void {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $date);
            });
        }

        $tickets = $query->orderBy('valid_from')->get();
        self::loadStats($tickets);

        return TicketResource::collection($tickets);
    }

    #[OA\Post(
        path: '/tickets',
        operationId: 'createTicket',
        description: 'Creates a new ticket for the currently authenticated user. Only available to users with the closed-beta role.',
        summary: 'Create a ticket',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'My BahnCard 100'),
                    new OA\Property(property: 'valid_from', type: 'string', format: 'date', example: '2026-01-01', nullable: true),
                    new OA\Property(property: 'valid_until', type: 'string', format: 'date', example: '2026-12-31', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 3199.00, nullable: true),
                    new OA\Property(property: 'currency', type: 'string', example: 'EUR', nullable: true),
                ],
            ),
        ),
        tags: ['Tickets'],
        responses: [
            new OA\Response(
                response: 201,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/TicketResource')],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: 'Forbidden – closed-beta role required'),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function store(StoreTicketRequest $request): TicketResource|JsonResponse
    {
        try {
            $this->authorize('create', Ticket::class);
        } catch (AuthorizationException) {
            return $this->sendError('This feature is not available for your account.', 403);
        }

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'name' => $request->validated('name'),
            'valid_from' => $request->validated('valid_from'),
            'valid_until' => $request->validated('valid_until'),
            'price' => $request->validated('price'),
            'currency' => $request->validated('currency'),
        ]);

        return new TicketResource($ticket);
    }

    #[OA\Get(
        path: '/tickets/{id}',
        operationId: 'getTicket',
        description: 'Returns a single ticket of the currently authenticated user.',
        summary: 'Get a ticket',
        security: [['passport' => []], ['token' => []]],
        tags: ['Tickets'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Ticket UUID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/TicketResource')],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function show(string $id): TicketResource|JsonResponse
    {
        $ticket = Ticket::find($id);

        if ($ticket === null) {
            return $this->sendError('Ticket not found.', 404);
        }

        try {
            $this->authorize('view', $ticket);
        } catch (AuthorizationException) {
            return $this->sendError('Ticket not found.', 404);
        }

        self::loadStats(collect([$ticket]));

        return new TicketResource($ticket);
    }

    #[OA\Put(
        path: '/tickets/{id}',
        operationId: 'updateTicket',
        description: 'Updates a ticket of the currently authenticated user.',
        summary: 'Update a ticket',
        security: [['passport' => []], ['token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'My BahnCard 100'),
                    new OA\Property(property: 'valid_from', type: 'string', format: 'date', example: '2026-01-01', nullable: true),
                    new OA\Property(property: 'valid_until', type: 'string', format: 'date', example: '2026-12-31', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 3199.00, nullable: true),
                    new OA\Property(property: 'currency', type: 'string', example: 'EUR', nullable: true),
                ],
            ),
        ),
        tags: ['Tickets'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Ticket UUID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/TicketResource')],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
            new OA\Response(response: 422, description: self::OA_DESC_UNPROCESSABLE),
        ],
    )]
    public function update(UpdateTicketRequest $request, string $id): TicketResource|JsonResponse
    {
        $ticket = Ticket::find($id);

        if ($ticket === null) {
            return $this->sendError('Ticket not found.', 404);
        }

        try {
            $this->authorize('update', $ticket);
        } catch (AuthorizationException) {
            return $this->sendError('Ticket not found.', 404);
        }

        $ticket->update($request->validated());

        return new TicketResource($ticket->fresh());
    }

    #[OA\Get(
        path: '/tickets/{id}/statistics',
        operationId: 'getTicketStatistics',
        description: 'Returns usage statistics for a single ticket of the currently authenticated user.',
        summary: 'Get statistics for a ticket',
        security: [['passport' => ['read-statistics']], ['token' => []]],
        tags: ['Tickets'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Ticket UUID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: self::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/TicketStatisticsResource')],
                ),
            ),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function statistics(string $id): TicketStatisticsResource|JsonResponse
    {
        $ticket = Ticket::find($id);

        if ($ticket === null) {
            return $this->sendError('Ticket not found.', 404);
        }

        try {
            $this->authorize('view', $ticket);
        } catch (AuthorizationException) {
            return $this->sendError('Ticket not found.', 404);
        }

        $base = DB::table('train_checkins')
            ->join('statuses', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.ticket_id', $ticket->id);

        $totals = (clone $base)->selectRaw('
            COUNT(*) AS trip_count,
            COALESCE(SUM(train_checkins.distance), 0) AS total_distance,
            COALESCE(SUM(train_checkins.duration), 0) AS total_duration,
            MIN(DATE(train_checkins.departure)) AS first_used,
            MAX(DATE(train_checkins.departure)) AS last_used
        ')->first();

        $tripCount = (int) $totals->trip_count;
        $distance = (int) $totals->total_distance;
        $duration = (int) $totals->total_duration;

        $costPerTrip = null;
        $costPerKm = null;
        $costPerHour = null;

        if ($ticket->price !== null && $tripCount > 0) {
            $price = (float) $ticket->price;
            $costPerTrip = round($price / $tripCount, 2);
            $costPerKm = $distance > 0 ? round($price / ($distance / 1000), 2) : null;
            $costPerHour = $duration > 0 ? round($price / ($duration / 60), 2) : null;
        }

        $purposes = DB::table('statuses')
            ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('statuses.ticket_id', $ticket->id)
            ->groupBy('statuses.business')
            ->selectRaw('statuses.business AS reason, COUNT(train_checkins.id) AS count, COALESCE(SUM(train_checkins.distance), 0) AS distance')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['reason' => $r->reason, 'count' => (int) $r->count, 'distance' => (int) $r->distance])
            ->values()
            ->all();

        $categories = (clone $base)
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->groupBy('hafas_trips.category')
            ->selectRaw('hafas_trips.category AS name, COUNT(*) AS count, COALESCE(SUM(train_checkins.distance), 0) AS distance')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'count' => (int) $r->count, 'distance' => (int) $r->distance])
            ->values()
            ->all();

        $operators = (clone $base)
            ->join('hafas_trips', 'train_checkins.trip_id', '=', 'hafas_trips.trip_id')
            ->leftJoin('operators', 'operators.id', '=', 'hafas_trips.operator_id')
            ->groupBy('operators.name')
            ->selectRaw('operators.name, COUNT(*) AS count, COALESCE(SUM(train_checkins.distance), 0) AS distance')
            ->orderByDesc('distance')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'count' => (int) $r->count, 'distance' => (int) $r->distance])
            ->values()
            ->all();

        return new TicketStatisticsResource(new TicketStatisticsDto(
            tripCount: $tripCount,
            distance: $distance,
            duration: $duration,
            firstUsed: $totals->first_used,
            lastUsed: $totals->last_used,
            costPerTrip: $costPerTrip,
            costPerKm: $costPerKm,
            costPerHour: $costPerHour,
            purposes: $purposes,
            categories: $categories,
            operators: $operators,
        ));
    }

    #[OA\Delete(
        path: '/tickets/{id}',
        operationId: 'deleteTicket',
        description: 'Deletes a ticket of the currently authenticated user. Associated statuses will have their ticket reference removed.',
        summary: 'Delete a ticket',
        security: [['passport' => []], ['token' => []]],
        tags: ['Tickets'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Ticket UUID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: self::OA_DESC_NO_CONTENT),
            new OA\Response(response: 401, description: self::OA_DESC_UNAUTHENTICATED),
            new OA\Response(response: 403, description: self::OA_DESC_FORBIDDEN),
            new OA\Response(response: 404, description: self::OA_DESC_NOT_FOUND),
        ],
    )]
    public function destroy(string $id): JsonResponse
    {
        $ticket = Ticket::find($id);

        if ($ticket === null) {
            return $this->sendError('Ticket not found.', 404);
        }

        try {
            $this->authorize('delete', $ticket);
        } catch (AuthorizationException) {
            return $this->sendError('Ticket not found.', 404);
        }

        $ticket->delete();

        return response()->json(null, 204);
    }
}
