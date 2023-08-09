<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\Auth\LoginController;
use App\Http\Resources\UserSettingsResource;
use App\Providers\AuthServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @deprecated Remove before 2023-10! Maybe earlier - if possible. Deprecation is already announced since
     *             November'22.
     */
    public function login(Request $request): JsonResponse {
        $validated = $request->validate(['login' => ['required', 'max:255'], 'password' => ['required', 'min:8', 'max:255']]);

        if (LoginController::login($validated['login'], $validated['password'])) {
            $token = $request->user()->createToken('token', array_keys(AuthServiceProvider::$scopes));
            return $this->sendResponse([
                                           'WARNING'    => 'This endpoint (login) is deprecated and will be removed in the following weeks. Please migrate to use OAuth2. More information: https://github.com/Traewelling/traewelling/issues/1772',
                                           'token'      => $token->accessToken,
                                           'expires_at' => $token->token->expires_at->toIso8601String(),
                                       ])
                        ->header('Authorization', $token->accessToken);
        }
        return $this->sendError('Non-matching credentials', 401);
    }

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
     *                      ref="#/components/schemas/UserAuth"
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
    public function user(Request $request): JsonResponse {
        return $this->sendResponse(new UserSettingsResource($request->user()));
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
                                       'expires_at' => $newToken->token->expires_at->toIso8601String()]
        )->header('Authorization', $newToken->accessToken);
    }
}
