<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Resources\UserAuthResource;
use App\Providers\AuthServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *      path="/auth/logout",
     *      operationId="logoutUser",
     *      tags={"Auth"},
     *      summary="Logout & invalidate current bearer token",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property (
     *                  property="status",
     *                  example="success"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=500, description="Error during revoke"),
     *       security={
     *          {"passport": {}}, {"token": {}}
     *       }
     *     )
     * @param Request $request
     *
     * @return JsonResponse
     * @api v1
     */
    public function logout(Request $request): JsonResponse {
        $isUser = $request->user()->token()->revoke();
        if ($isUser) {
            return $this->sendResponse();
        }
        return $this->sendResponse('unknown', 500);
    }

    /**
     * @OA\Get(
     *      path="/auth/user",
     *      operationId="getAuthenticatedUser",
     *      tags={"Auth", "User"},
     *      summary="Get authenticated user information",
     *      description="Get all profile information about the authenticated user",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                      ref="#/components/schemas/UserAuthResource"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       security={
     *          {"passport": {}}, {"token": {}}
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return UserAuthResource
     * @api v1
     */
    public function user(Request $request) {
        return new UserAuthResource($request->user());
    }

    /**
     * @OA\Post(
     *      path="/auth/refresh",
     *      operationId="refreshToken",
     *      tags={"Auth"},
     *      summary="Refresh Bearer Token",
     *      description="This request issues a new Bearer-Token with a new expiration date while also revoking the old
     *      token.",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                      ref="#/components/schemas/BearerTokenResponse"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=401, description="Unauthorized"),
     *       security={
     *          {"passport": {}}, {"token": {}}
     *       }
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @api v1
     */
    public function refresh(Request $request): JsonResponse {
        $oldToken = $request->user()->token();
        $newToken = $request->user()->createToken('token', array_keys(AuthServiceProvider::$scopes));
        $oldToken->revoke();
        return $this->sendResponse([
                                       'token'      => $newToken->accessToken,
                                       'expires_at' => $newToken->token->expires_at->toIso8601String()
                                   ])
                    ->header('Authorization', $newToken->accessToken);
    }
}
