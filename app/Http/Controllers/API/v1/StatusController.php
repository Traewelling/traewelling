<?php


namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\GeoController;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Resources\PolylineResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\StopoverResource;
use App\Models\HafasTrip;
use App\Models\Status;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class StatusController extends ResponseController
{
    /**
     * @OA\Get(
     *      path="/dashboard",
     *      operationId="getDashboard",
     *      tags={"Dashboard"},
     *      summary="Get paginated statuses of personal dashboard",
     *      description="Returns paginated statuses of personal dashboard",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Status"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       security={
     *           {"api_key_security_example": {}}
     *       }
     *     )
     *
     * Returns list of projects
     */
    public static function getDashboard(): AnonymousResourceCollection {
        return StatusResource::collection(DashboardController::getPrivateDashboard(Auth::user()));
    }

    public static function getGlobalDashboard(): AnonymousResourceCollection {
        return StatusResource::collection(DashboardController::getGlobalDashboard(Auth::user()));
    }

    public static function getFutureCheckins(): AnonymousResourceCollection {
        return StatusResource::collection(StatusBackend::getFutureCheckins());
    }

    public function enRoute(): AnonymousResourceCollection {
        return StatusResource::collection(StatusBackend::getActiveStatuses(null, false)['statuses']);
    }

    /**
     * Show single status
     *
     * @param int $id
     *
     * @return StatusResource|Response
     */
    public function show(int $id): StatusResource|Response {
        $status = StatusBackend::getStatus($id);
        try {
            $this->authorize('view', $status);
        } catch (AuthorizationException) {
            abort(403, 'Status invisible to you.');
        }
        return new StatusResource($status);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse {
        try {
            StatusBackend::DeleteStatus(Auth::user(), $id);
        } catch (PermissionException) {
            abort(403);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return $this->sendv1Response();
    }

    /**
     * @param Request $request
     * @param int     $statusId
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, int $statusId): JsonResponse {
        $validator = Validator::make($request->all(), [
            'body'       => ['nullable', 'max:280', 'nullable'],
            'business'   => ['required', new Enum(Business::class)],
            'visibility' => ['required', new Enum(StatusVisibility::class)],
        ]);

        if ($validator->fails()) {
            return $this->sendv1Error($validator->errors(), 400);
        }
        $validated = $validator->validate();

        try {
            $editStatusResponse = StatusBackend::EditStatus(
                user:       Auth::user(),
                statusId:   $statusId,
                body:       $validated['body'],
                business:   Business::from($validated['business']),
                visibility: StatusVisibility::from($validated['visibility']),
            );
            return $this->sendv1Response(new StatusResource($editStatusResponse));
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (PermissionException) {
            abort(403);
        }
    }

    /**
     * @param string $parameters
     *
     * @return JsonResponse
     * @todo extract this to backend
     * @todo does this conform to the private checkin-shit?
     */
    public function getPolyline(string $parameters): JsonResponse {
        $ids             = explode(',', $parameters, 50);
        $geoJsonFeatures = Status::whereIn('id', $ids)
                                 ->with('trainCheckin.HafasTrip.polyline')
                                 ->get()
                                 ->filter(function(Status $status) {
                                     return \request()?->user()->can('view', $status);
                                 })
                                 ->map(function($status) {
                                     return [
                                         'type'       => 'Feature',
                                         'geometry'   => [
                                             'type'        => 'LineString',
                                             'coordinates' => GeoController::getMapLinesForCheckin($status->trainCheckin)
                                         ],
                                         'properties' => [
                                             'statusId' => $status->id
                                         ]
                                     ];
                                 });
        $geoJson         = [
            'type'     => 'FeatureCollection',
            'features' => $geoJsonFeatures
        ];
        return $ids ? $this->sendv1Response($geoJson) : $this->sendv1Error("");
    }

    /**
     * @param string $parameters
     *
     * @return JsonResponse
     */
    public function getStopovers(string $parameters): JsonResponse {
        $tripIds = explode(',', $parameters, 50);
        $trips   = HafasTrip::whereIn('id', $tripIds)->get()->mapWithKeys(function($trip) {
            return [$trip->id => StopoverResource::collection($trip->stopoversNEW)];
        });
        return $this->sendv1Response($trips);
    }
}
