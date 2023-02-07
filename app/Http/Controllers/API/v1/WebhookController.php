<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\WebhookController as WebhookBackend;
use App\Http\Resources\WebhookResource;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/webhooks",
     *     operationId="getWebhooks",
     *     tags={"Webhooks"},
     *     summary="Get webhooks for current user and current application",
     *     description="Returns all webhooks which are created for the current user and which the current authorized applicaton has access to.",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Webhook"
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={
     *         {"passport": {}}, {"token": {}}
     *     }
     * )
     */
    public function getWebhooks(Request $request): AnonymousResourceCollection
    {
        $clientId = $request->user()->token()->client->id;
        $webhooks = Webhook::where('oauth_client_id', '=', $clientId)
            ->where('user_id', '=', $request->user()->id)
            ->get();
        return WebhookResource::collection($webhooks);
    }

    /**
     * @OA\Get(
     *      path="/webhooks/{id}",
     *      operationId="getSingleWebhook",
     *      tags={"Webhooks"},
     *      summary="Get single webhook",
     *      description="Returns a single webhook Object, if user and application is authorized to see it",
     *      @OA\Parameter (
     *          name="id",
     *          in="path",
     *          description="Webhook-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data",
     *                      ref="#/components/schemas/Webhook"
     *              ),
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No webhook found or unauthorized for this id"),
     *       security={
     *           {"passport": {}}, {"token": {}}
     *       }
     *     )
     *
     * Show single webhook
     *
     * @param int $webhookId
     *
     * @return WebhookResource|JsonResponse
     */
    public function getWebhook(Request $request, int $webhookId): WebhookResource|JsonResponse
    {
        $clientId = $request->user()->token()->client->id;
        $webhook = Webhook::where('oauth_client_id', '=', $clientId)
            ->where('user_id', '=', $request->user()->id)
            ->where('id', '=', $webhookId)
            ->first();
        if ($webhook == null) {
            return $this->sendError('No webhook found for this id.');
        }
        return new WebhookResource($webhook);
    }

    /**
     * @OA\Delete(
     *      path="/webhooks/{id}",
     *      operationId="deleteWebhook",
     *      tags={"Webhooks"},
     *      summary="Delete a webhook if the user and application are authorized to do",
     *      description="",
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
     *                      ref="#/components/schemas/SuccessResponse"
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No webhook found for this id"),
     *       @OA\Response(response=403, description="User or application not authorized to delete this webhook"),
     *       security={
     *           {"passport": {}}, {"token": {}}
     *       }
     *     )
     *
     * @param int $webhookId
     *
     * @return JsonResponse
     */
    public function deleteWebhook(Request $request, int $webhookId): JsonResponse
    {
        try {
            $webhook = Webhook::findOrFail($webhookId);
            WebhookBackend::deleteWebhook($request->user(), $request->user()->token()->client, $webhook);
            return $this->sendResponse();
        } catch (PermissionException) {
            return $this->sendError('You are not allowed to delete this webhook', 403);
        } catch (ModelNotFoundException) {
            return $this->sendError('No webhook found for this id.');
        }
    }
}
