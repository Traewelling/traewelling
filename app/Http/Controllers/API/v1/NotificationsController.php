<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\UserNotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/notifications/unread/count",
     *      operationId="getUnreadCount",
     *      tags={"Notifications"},
     *      summary="Get count of unread notifications for authenticated user",
     *      description="Returns count of unread notifications of a authenticated user",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="integer",
     *                  example=2
     *              ),
     *          )
     *       ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *       security={
     *           {"passport": {}}, {"token": {}}
     *       },
     *       security={
     *           {"passport": {"read-notifications"}}, {"token": {}}
     *       }
     *     )
     */
    public function getUnreadCount(): JsonResponse {
        return $this->sendResponse(Auth::user()->unreadNotifications->count());
    }

    /**
     * @OA\Get(
     *      path="/notifications",
     *      operationId="listNotifications",
     *      tags={"Notifications"},
     *      summary="Get paginated notifications for authenticated user",
     *      description="Returns paginated notifications of a authenticated",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Notification"
     *                  )
     *              ),
     *              @OA\Property(property="links", ref="#/components/schemas/Links"),
     *              @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     *          )
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       security={
     *           {"passport": {}}, {"token": {}}
     *       },
     *       security={
     *           {"passport": {"read-notifications"}}, {"token": {}}
     *       }
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function listNotifications(): AnonymousResourceCollection {
        return UserNotificationResource::collection(Auth::user()->notifications()->simplePaginate(15));
    }

    /**
     * @OA\Put(
     *      path="/notifications/read/{id}",
     *      operationId="markAsRead",
     *      tags={"Notifications"},
     *      summary="Mark notification as read",
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="ID of notification",
     *          example="cbf6054e-9c00-4b1f-ab37-7eb18ac8419f",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/Notification")
     *         )
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Notification not found"),
     *       security={
     *           {"passport": {"write-notifications"}}, {"token": {}}
     *       }
     *     )
     */
    public function markAsRead(string $notificationId): JsonResponse {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification === null) {
            return $this->sendError();
        }

        $notification->markAsRead();
        return $this->sendResponse(new UserNotificationResource($notification));
    }

    /**
     * @OA\Put(
     *      path="/notifications/unread/{id}",
     *      operationId="markAsUnread",
     *      tags={"Notifications"},
     *      summary="Mark notification as unread",
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="ID of notification",
     *          example="cbf6054e-9c00-4b1f-ab37-7eb18ac8419f",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object", ref="#/components/schemas/Notification")
     *         )
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       @OA\Response(response=404, description="Notification not found"),
     *       security={
     *           {"passport": {"write-notifications"}}, {"token": {}}
     *       }
     *     )
     */
    public function markAsUnread(string $notificationId): JsonResponse {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification === null) {
            return $this->sendError();
        }

        $notification->markAsUnread();
        return $this->sendResponse(new UserNotificationResource($notification));
    }

    /**
     * @OA\Put(
     *      path="/notifications/read/all",
     *      operationId="markAllAsRead",
     *      tags={"Notifications"},
     *      summary="Mark all notification as read",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      example="All notifications marked as read"
     *                  ),
     *              ),
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      security={
     *          {"passport": {"write-notifications"}}, {"token": {}}
     *      }
     *  )
     */
    public function markAllAsRead(): JsonResponse {
        Auth::user()->unreadNotifications->markAsRead();
        return $this->sendResponse(['message' => __('notifications.readAll.success')]);
    }
}
