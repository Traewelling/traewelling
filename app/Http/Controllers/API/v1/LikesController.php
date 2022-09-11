<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Resources\UserResource;
use App\Models\Like;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class LikesController extends ResponseController
{
    /**
     * @OA\Get(
     *      path="/statuses/{id}/likedby",
     *      operationId="getLikesForStatus",
     *      tags={"Likes"},
     *      summary="[Auth optional] Get likes for status",
     *      description="Returns array of users that liked the status",
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              ),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to access this status"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     * @param int $status
     *
     * @return AnonymousResourceCollection
     * @todo maybe put this in separate controller?
     */
    public function show(int $status): AnonymousResourceCollection {
        return UserResource::collection(
            User::whereIn('id', Like::where('status_id', $status)->select('user_id'))->get()
        );
    }

    /**
     * @param int $statusId
     *
     * @return JsonResponse
     * @throws PermissionException
     */
    public function create(int $statusId): JsonResponse {
        try {
            $status = Status::findOrFail($statusId);
            StatusBackend::createLike(Auth::user(), $status);
            return $this->sendv1Response(null, 201);
        } catch (StatusAlreadyLikedException) {
            abort(404);
        }
    }

    /**
     * @param int $statusId
     * @return JsonResponse
     */
    public function destroy(int $statusId): JsonResponse {
        try {
            StatusBackend::destroyLike(Auth::user(), $statusId);
            return $this->sendv1Response();
        } catch (InvalidArgumentException) {
            abort(404);
        }
    }
}
