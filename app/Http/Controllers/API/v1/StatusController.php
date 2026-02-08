<?php

namespace App\Http\Controllers\API\v1;

use App\Dto\GeoJson\Feature;
use App\Dto\GeoJson\FeatureCollection;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Events\StatusUpdateEvent;
use App\Http\Controllers\Backend\Support\LocationController;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Http\Resources\StatusResource;
use App\Http\Resources\StopoverResource;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\Trip;
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

class StatusController extends Controller
{
    /**
     * @OA\Get(
     *      path="/dashboard",
     *      operationId="getDashboard",
     *      tags={"Dashboard"},
     *      summary="Get paginated statuses of personal dashboard",
     *      description="Returns paginated statuses of personal dashboard",
     *
     *      @OA\Parameter (
     *          name="page",
     *          description="Page of pagination",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(type="integer")
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
     *                      ref="#/components/schemas/StatusResource"
     *                  )
     *              ),
     *
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Not logged in"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     */
    public static function getDashboard(): AnonymousResourceCollection
    {
        return StatusResource::collection(DashboardController::getPrivateDashboard(Auth::user()));
    }

    /**
     * @OA\Get(
     *      path="/dashboard/future",
     *      operationId="getFutureDashboard",
     *      tags={"Dashboard"},
     *      summary="Get paginated future statuses of current user",
     *      description="Returns paginated statuses of the authenticated user, that are more than 20 minutes in the
     *      future",
     *
     *      @OA\Parameter (
     *          name="page",
     *          description="Page of pagination",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(type="integer")
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
     *                      ref="#/components/schemas/StatusResource"
     *                  )
     *              ),
     *
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Not logged in"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     */
    public static function getFutureCheckins(): AnonymousResourceCollection
    {
        return StatusResource::collection(StatusBackend::getFutureCheckins());
    }

    /**
     * @OA\Get(
     *      path="/statuses",
     *      operationId="getActiveStatuses",
     *      tags={"Status"},
     *      summary="[Auth optional] Get active statuses",
     *      description="Returns all currently active statuses that are visible to the (un)authenticated user",
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
     *                      ref="#/components/schemas/StatusResource"
     *                  )
     *              ),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     */
    public function enRoute(): AnonymousResourceCollection
    {
        return StatusResource::collection(StatusBackend::getActiveStatuses());
    }

    /**
     * @OA\Get(
     *     path="/positions",
     *     operationId="getLivePositionsForActiveStatuses",
     *     tags={"Status"},
     *     summary="[Auth optional] get live positions for active statuses",
     *     description="Returns an array of live position objects for active statuses",
     *
     *     @OA\Response(
     *         response="200",
     *         description="successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     ref="#/components/schemas/LivePointDto"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="User not authorized to access this status"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     * )
     */
    public function livePositions(): JsonResource
    {
        return JsonResource::collection(StatusBackend::getLivePositions());
    }

    /**
     * @OA\Get(
     *     path="/positions/{ids}",
     *     operationId="getLivePositionsForStatuses",
     *     tags={"Status"},
     *     summary="[Auth optional] get live positions for given statuses",
     *     description="Returns an array of live position objects for given status IDs",
     *
     *     @OA\Parameter(
     *         name="ids",
     *         in="path",
     *         description="Status-IDs separated by comma",
     *         example="1337,1338",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     ref="#/components/schemas/LivePointDto"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="User not authorized to access this status"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     * )
     */
    public function getLivePositionForStatus($ids): AnonymousResourceCollection
    {
        return JsonResource::collection(StatusBackend::getLivePositionForStatus($ids));
    }

    /**
     * @OA\Get(
     *      path="/status",
     *      operationId="listStatuses",
     *      tags={"Status"},
     *      summary="[Auth optional] List and filter statuses",
     *      description="Returns paginated list of statuses, filtered by given parameters",
     *
     *      @OA\Parameter(
     *          name="body",
     *          in="query",
     *          description="Filter by text in status body",
     *          example="Having a great trip!",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="user_id",
     *          in="query",
     *          description="Filter by user ID",
     *          example=42,
     *
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Parameter(
     *          name="origin_text",
     *          in="query",
     *          description="Filter by origin station name",
     *          example="Central Station",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="origin_id",
     *          in="query",
     *          description="Filter by origin station ID",
     *          example=5,
     *
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Parameter(
     *          name="destination_text",
     *          in="query",
     *          description="Filter by destination station name",
     *          example="Main Square",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="destination_id",
     *          in="query",
     *          description="Filter by destination station ID",
     *          example=10,
     *
     *          @OA\Schema(type="integer")
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="list of matching statuses",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(
     *                      ref="#/components/schemas/StatusResource"
     *                  )
     *              )
     *          )
     *     )
     *  )
     */
    public function list(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            // generic filters
            'body' => ['nullable', 'string', 'max:32'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],

            // Filters for origin/destination
            'origin_text' => ['nullable', 'string', 'max:64'],
            'origin_id' => ['nullable', 'integer', 'exists:train_stations,id'],
            'destination_text' => ['nullable', 'string', 'max:64'],
            'destination_id' => ['nullable', 'integer', 'exists:train_stations,id'],
        ]);

        $user = auth()->user();
        $query = Status::query()->orderByDesc('created_at');

        if (isset($validated['body'])) {
            $query->where('body', 'like', '%' . $validated['body'] . '%');
        }

        $query->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
            ->join('users', 'statuses.user_id', '=', 'users.id')
            ->join('train_stopovers as origin_stopover', 'train_checkins.origin_stopover_id', '=', 'origin_stopover.id')
            ->join('train_stations as origin_station', 'origin_stopover.train_station_id', '=', 'origin_station.id')
            ->join('train_stopovers as destination_stopover', 'train_checkins.destination_stopover_id', '=', 'destination_stopover.id')
            ->join('train_stations as destination_station', 'destination_stopover.train_station_id', '=', 'destination_station.id')
            ->when(isset($validated['origin_text']), function ($q) use ($validated) {
                $q->where('origin_station.name', 'like', '%' . $validated['origin_text'] . '%');
            })
            ->when(isset($validated['origin_id']), function ($q) use ($validated) {
                $q->where('origin_station.id', $validated['origin_id']);
            })
            ->when(isset($validated['destination_text']), function ($q) use ($validated) {
                $q->where('destination_station.name', 'like', '%' . $validated['destination_text'] . '%');
            })
            ->when(isset($validated['destination_id']), function ($q) use ($validated) {
                $q->where('destination_station.id', $validated['destination_id']);
            })
            ->when(isset($validated['user_id']), function ($q) use ($validated) {
                $q->where('users.id', $validated['user_id']);
            })
            ->where(\App\Http\Controllers\Backend\Transport\StatusController::filterStatusVisibility($user))
            ->where('train_checkins.departure', '<', now()->addMinutes(20))
            ->whereNotIn('statuses.user_id', $user->mutedUsers()->select('muted_id'))
            ->whereNotIn('statuses.user_id', $user->blockedUsers()->select('blocked_id'))
            ->whereNotIn('statuses.user_id', $user->blockedByUsers()->select('user_id'))
            ->select('statuses.*');

        return StatusResource::collection($query->cursorPaginate(20));
    }

    /**
     * @OA\Get(
     *      path="/status/{id}",
     *      operationId="getSingleStatus",
     *      tags={"Status"},
     *      summary="[Auth optional] Get single statuses",
     *      description="Returns a single status Object, if user is authorized to see it",
     *
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data",
     *                      ref="#/components/schemas/StatusResource"
     *              ),
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to access this status"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * Show single status
     */
    public function show(int $id): StatusResource|JsonResponse
    {
        $status = StatusBackend::getStatus($id);
        try {
            $this->authorize('view', $status);
        } catch (AuthorizationException) {
            return response()->json(['message' => 'Status invisible to you.'], 403);
        }

        return new StatusResource($status);
    }

    /**
     * @OA\Delete(
     *      path="/status/{id}",
     *      operationId="destroySingleStatus",
     *      tags={"Status"},
     *      summary="Destroy a status",
     *      description="Deletes a single status Object, if user is authorized to",
     *
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\Response(response=204, description="Status deleted."),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="No status found for this id"),
     *      @OA\Response(response=403, description="User not authorized to manipulate this status"),
     *      security={
     *          {"passport": {"write-statuses"}}, {"token": {}}
     *      }
     * )
     */
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
     * @OA\Put(
     *      path="/status/{id}",
     *      operationId="updateSingleStatus",
     *      tags={"Status"},
     *      summary="Update a status",
     *      description="Updates a single status Object, if user is authorized to",
     *
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *
     *          @OA\Schema(type="integer")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/StatusUpdateBody")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/StatusResource"
     *              )
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to manipulate this status"),
     *       security={
     *           {"passport": {"write-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @throws ValidationException
     */
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
                    ->where("trip_id", $status->checkin->trip->trip_id)
                    ->first();

                if ($stopover === null) {
                    return $this->sendError('Invalid stopover given', 400);
                }

                TrainCheckinController::changeDestination(
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

    /**
     * @OA\Get(
     *      path="/polyline/{ids}",
     *      operationId="getPolylines",
     *      tags={"Status"},
     *      summary="[Auth optional] Get GeoJSON for statuses",
     *      description="Returns GeoJSON for all requested status IDs",
     *
     *      @OA\Parameter (
     *          name="ids",
     *          in="path",
     *          description="comma seperated status IDs",
     *          example="1337,1338",
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
     *              @OA\Property (
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="type",
     *                      example="FeatureCollection"
     *                  ),
     *                  @OA\Property (
     *                      property="features", type="array",
     *
     *                      @OA\Items (
     *                          ref="#/components/schemas/Polyline"
     *                      ),
     *                  ),
     *              )
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to access this status"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *
     *       }
     *     )
     *
     * @todo extract this to backend
     * @todo does this conform to the private checkin-shit?
     */
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

    /**
     ** @OA\Get(
     *      path="/stopovers/{ids}",
     *      operationId="getStopOvers",
     *      tags={"Status"},
     *      summary="[Auth optional] Get stopovers for statuses",
     *      description="Returns all underway-stops for stations",
     *
     *      @OA\Parameter (
     *          name="ids",
     *          in="path",
     *          description="comma seperated trip IDs",
     *          example="1,2",
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
     *              @OA\Property (
     *                  property="data", type="object",
     *                  @OA\Property(
     *                      property="1", type="array", description="Array of stopovers. Key describes trip id",
     *
     *                      @OA\Items(ref="#/components/schemas/StopoverResource")
     *                  )
     *              )
     *          )
     *       ),
     *
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to access this status"),
     *       security={
     *           {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     */
    public function getStopovers(string $parameters): JsonResponse
    {
        $tripIds = explode(',', $parameters, 50);
        $trips = Trip::whereIn('id', $tripIds)->get()->mapWithKeys(function ($trip) {
            return [$trip->id => StopoverResource::collection($trip->stopovers)];
        });

        return $this->sendResponse($trips);
    }

    /**
     * @OA\Get(
     *      path="/user/statuses/active",
     *      operationId="userState",
     *      tags={"Auth"},
     *      summary="User state",
     *      description="This request returns whether the currently logged-in user has an active check-in or not.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="object",
     *                      ref="#/components/schemas/StatusResource"
     *              )
     *          )
     *       ),
     *
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=204, description="No active checkin"),
     *       security={
     *          {"passport": {"read-statuses"}}, {"token": {}}
     *       }
     *     )
     */
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
