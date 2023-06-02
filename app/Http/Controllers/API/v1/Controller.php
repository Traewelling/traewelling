<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *      version="1.0.0 - alpha",
 *      title="Träwelling API",
 *      description="Träwelling user API description. This is an incomplete documentation with still many errors. The API is currently not yet stable. Endpoints are still being restructured. Both the URL and the request or body can be changed. Breaking changes will be announced on the Discord server: https://discord.gg/72t7564ZbV",
 *      @OA\Contact(
 *          email="support@traewelling.de"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server (
 *     url="https://traewelling.de/api/v1",
 *     description="Production Server"
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="This instance"
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="Logging in, creating Accounts, etc."
 * )
 * @OA\Tag(
 *     name="Checkin",
 *     description="Checkin related endpoints. Regular process is departures -> trip -> checkin"
 * )
 * @OA\Tag(
 *     name="Events",
 *     description="Events that users can check in to"
 * )
 * @OA\Tag(
 *     name="Status",
 *     description="Endpoints for accessing and manipulating Statusses and their additional data"
 * )
 * @OA\Tag(
 *     name="Dashboard",
 *     description="API Endpoints of Dashboard"
 * )
 * @OA\Tag(
 *     name="Likes",
 *     description="Likes regarding a single status"
 * )
 * @OA\Tag(
 *     name="User",
 *     description="Information regarding users"
 * )
 * @OA\Tag(
 *     name="User/Follow",
 *     description="Follow and unfollow users, manage your followers"
 * )
 * @OA\Tag(
 *     name="User/Hide and Block",
 *     description="Mute and block users"
 * )
 * @OA\Tag(
 *     name="Leaderboard",
 *     description="Leaderboard related endpoints"
 * )
 * @OA\Tag(
 *     name="Statistics",
 *     description="Statistics related endpoints"
 * )
 * @OA\Tag(
 *     name="Settings",
 *     description="User/Profile-Settings"
 * )
 * @OA\Tag(
 *     name="Webhooks",
 *     description="Manage Webhooks for third party applications"
 * )
 */
class Controller extends \App\Http\Controllers\Controller
{
    public function sendResponse(
        array|string|object $data = null,
        int                 $code = 200,
        array               $additional = null
    ): JsonResponse {
        $disclaimer = 'APIv1 is not officially released for use and is also not fully documented. You can find the documentation at https://traewelling.de/api/documentation. Use at your own risk. Data fields may change at any time without notice.';
        if ($data === null) {
            return response()->json(
                data:   [
                            'disclaimer' => $disclaimer,
                            'status'     => 'success',
                        ],
                status: $code
            );
        }
        $response = [
            'disclaimer' => $disclaimer,
            'data'       => $data,
        ];
        $response = $additional ? array_merge($response, $additional) : $response;
        return response()->json($response, $code);
    }

    public function sendError(array|string $error = null, int $code = 404, array $additional = null): JsonResponse {
        $response = [
            'message' => $error,
        ];
        $response = $additional ? array_merge($response, ["meta" => $additional]) : $response;
        return response()->json($response, $code);
    }
}
