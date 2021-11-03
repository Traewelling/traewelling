<?php


namespace App\Http\Controllers\API\v1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Exceptions\PermissionException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Resources\PolylineResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\StopoverResource;
use App\Models\HafasTrip;
use App\Models\Status;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StatusController extends ResponseController
{
    public static function getDashboard(): AnonymousResourceCollection {
        return StatusResource::collection(StatusBackend::getDashboard(Auth::user()));
    }

    public static function getGlobalDashboard(): AnonymousResourceCollection {
        return StatusResource::collection(StatusBackend::getGlobalDashboard());
    }

    public static function getFutureCheckins(): AnonymousResourceCollection {
        return StatusResource::collection(StatusBackend::getFutureCheckins());
    }

    public function enRoute(): AnonymousResourceCollection {
        return StatusResource::collection(StatusBackend::getActiveStatuses(null, false)['statuses']);
    }

    /**
     * Show single status
     * @param int $id
     * @return StatusResource|Response
     */
    public function show(int $id): StatusResource|Response {
        return new StatusResource(StatusBackend::getStatus($id));
    }

    /**
     * @param int $id
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
     * @param int $statusId
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, int $statusId): JsonResponse {
        $validator = Validator::make($request->all(), [
            'body'       => ['nullable', 'max:280', 'nullable'],
            'business'   => ['required', Rule::in(Business::getList())],
            'visibility' => ['required', Rule::in(StatusVisibility::getList())],
        ]);

        if ($validator->fails()) {
            return $this->sendv1Error($validator->errors(), 400);
        }
        $validated = $validator->validate();

        try {
            $editStatusResponse = StatusBackend::EditStatus(
                user: Auth::user(),
                statusId: $statusId,
                body: $validated['body'],
                business: $validated['business'],
                visibility: $validated['visibility']
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
     * @return JsonResponse
     * @todo extract this to backend
     * @todo does this conform to the private checkin-shit?
     */
    public function getPolyline(string $parameters): JsonResponse {
        $ids      = explode(',', $parameters, 50);
        $mapLines = Status::whereIn('id', $ids)
                          ->with('trainCheckin.HafasTrip.polyline')
                          ->get()
                          ->reject(function($status) {
                              return ($status->user->userInvisibleToMe
                                      || ($status->statusInvisibleToMe
                                          && $status->visibility !== StatusVisibility::UNLISTED
                                      ));
                          })
                          ->mapWithKeys(function($status) {
                              return [$status->id => $status->trainCheckin->getMapLines()];
                          });
        return $ids ? $this->sendv1Response($mapLines) : $this->sendv1Error("");
    }

    /**
     * @param string $parameters
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
