<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\UserResource;
use App\Models\Status;
use App\Models\StatusHiddenUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StatusHiddenUserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/status/{statusId}/hidden-users",
     *      operationId="getHiddenUsers",
     *      tags={"Status"},
     *      summary="Get users hidden from viewing a status",
     *      description="Returns list of users who are hidden from viewing this specific status",
     *      @OA\Parameter (
     *          name="statusId",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/UserResource")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=403, description="User not authorized"),
     *      @OA\Response(response=404, description="Status not found"),
     *      security={
     *          {"passport": {"read-statuses"}}, {"token": {}}
     *      }
     * )
     *
     * @param int $statusId
     * @return JsonResponse
     */
    public function index(int $statusId): JsonResponse {
        try {
            $status = Status::findOrFail($statusId);
            $this->authorize('update', $status);

            $hiddenUsers = $status->hiddenUsers()->with('user')->get()->pluck('user');

            return $this->sendResponse(UserResource::collection($hiddenUsers));
        } catch (ModelNotFoundException) {
            return $this->sendError('Status not found', 404);
        } catch (AuthorizationException) {
            return $this->sendError('You are not authorized to view hidden users for this status', 403);
        }
    }

    /**
     * @OA\Post(
     *      path="/status/{statusId}/hidden-users",
     *      operationId="addHiddenUser",
     *      tags={"Status"},
     *      summary="Add a user to the hidden list for a status",
     *      description="Adds a user who will not be able to see this specific status",
     *      @OA\Parameter (
     *          name="statusId",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"userId"},
     *              @OA\Property(property="userId", type="integer", example=42,
     *                  description="ID of user to hide this status from")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User successfully added to hidden list",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User added to hidden list")
     *          )
     *      ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=403, description="User not authorized"),
     *      @OA\Response(response=404, description="Status or user not found"),
     *      @OA\Response(response=409, description="User already hidden"),
     *      security={
     *          {"passport": {"write-statuses"}}, {"token": {}}
     *      }
     * )
     *
     * @param Request $request
     * @param int $statusId
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, int $statusId): JsonResponse {
        $validator = Validator::make($request->all(), [
            'userId' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $validated = $validator->validate();

        try {
            $status = Status::findOrFail($statusId);
            $this->authorize('update', $status);

            $userToHide = User::findOrFail($validated['userId']);

            // Can't hide yourself from your own status
            if ($userToHide->id === $status->user_id) {
                return $this->sendError('You cannot hide yourself from your own status', 400);
            }

            // Check if already hidden
            if ($status->hiddenUsers()->where('user_id', $userToHide->id)->exists()) {
                return $this->sendError('User is already hidden from this status', 409);
            }

            StatusHiddenUser::create([
                'status_id' => $status->id,
                'user_id'   => $userToHide->id,
            ]);

            return $this->sendResponse(['message' => __('status.hidden-user.added')], 201);
        } catch (ModelNotFoundException) {
            return $this->sendError('Status or user not found', 404);
        } catch (AuthorizationException) {
            return $this->sendError('You are not authorized to modify hidden users for this status', 403);
        }
    }

    /**
     * @OA\Delete(
     *      path="/status/{statusId}/hidden-users/{userId}",
     *      operationId="removeHiddenUser",
     *      tags={"Status"},
     *      summary="Remove a user from the hidden list for a status",
     *      description="Removes a user from the hidden list, allowing them to see this status again",
     *      @OA\Parameter (
     *          name="statusId",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter (
     *          name="userId",
     *          in="path",
     *          description="User-ID to remove from hidden list",
     *          example=42,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User successfully removed from hidden list",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User removed from hidden list")
     *          )
     *      ),
     *      @OA\Response(response=403, description="User not authorized"),
     *      @OA\Response(response=404, description="Status or hidden user entry not found"),
     *      security={
     *          {"passport": {"write-statuses"}}, {"token": {}}
     *      }
     * )
     *
     * @param int $statusId
     * @param int $userId
     * @return JsonResponse
     */
    public function destroy(int $statusId, int $userId): JsonResponse {
        try {
            $status = Status::findOrFail($statusId);
            $this->authorize('update', $status);

            $hiddenEntry = $status->hiddenUsers()->where('user_id', $userId)->first();

            if (!$hiddenEntry) {
                return $this->sendError('User is not hidden from this status', 404);
            }

            $hiddenEntry->delete();

            return $this->sendResponse(['message' => __('status.hidden-user.removed')]);
        } catch (ModelNotFoundException) {
            return $this->sendError('Status not found', 404);
        } catch (AuthorizationException) {
            return $this->sendError('You are not authorized to modify hidden users for this status', 403);
        }
    }
}
