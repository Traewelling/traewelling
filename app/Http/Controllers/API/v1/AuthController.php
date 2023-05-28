<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\Auth\LoginController;
use App\Http\Resources\UserSettingsResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Providers\AuthServiceProvider;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/auth/signup",
     *      operationId="registerUser",
     *      tags={"Auth"},
     *      summary="register new user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  minLength=3,
     *                  maxLength=25,
     *                  pattern="^[a-zA-Z0-9_]*$",
     *                  description="Username",
     *                  example="Gertrud123"
     *              ),
     *              @OA\Property (
     *                  property="name",
     *                  type="string",
     *                  maxLength=50,
     *              ),
     *              @OA\Property (
     *                  property="email",
     *                  example="mail@example.com"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="password",
     *                  type="string",
     *                  minLength=8,
     *                  maxLength=255,
     *                  example="thisisnotasecurepassword123"
     *              ),
     *              @OA\Property (
     *                  property="password_confirmation",
     *                  description="confirmation of the password-field.",
     *                  type="string",
     *                  minLength=8,
     *                  maxLength=255,
     *                  example="thisisnotasecurepassword123"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                      ref="#/components/schemas/BearerTokenResponse"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=401, description="Other (not specified) error occured"),
     *       @OA\Response(response=422, description="Username or email is already taken, or other input error")
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @api v1
     */
    public function register(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'unique:users', 'min:3', 'max:25', 'regex:/^[a-zA-Z0-9_]*$/'],
            'name'     => ['required', 'max:50'],
            'email'    => ['required', 'email', 'unique:users', 'max:255'],
            'password' => ['required', 'confirmed', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                                        'status' => 'error',
                                        'errors' => $validator->errors()
                                    ], 422);
        }

        $validated               = $validator->validated();
        $validated['password']   = Hash::make($validated['password']);
        $validated['last_login'] = now();
        $user                    = User::create($validated);

        if ($user->wasRecentlyCreated) {
            $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes));
            return $this->sendResponse(
                data: [
                          'token'      => $userToken->accessToken,
                          'expires_at' => $userToken->token->expires_at->toIso8601String()
                      ],
                code: 201
            );
        }
        return $this->sendError("Sorry! Registration is not successful.", 401);
    }

    /**
     * @OA\Post(
     *      path="/auth/login",
     *      operationId="loginUser",
     *      tags={"Auth"},
     *      summary="Login with username & password",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="login",
     *                  type="string",
     *                  minLength=8,
     *                  maxLength=255,
     *                  description="Username or email",
     *                  example="gertrud@traewelling.de"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="password",
     *                  type="string",
     *                  minLength=8,
     *                  maxLength=255,
     *                  example="thisisnotasecurepassword123"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                      ref="#/components/schemas/BearerTokenResponse"
     *              )
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=401, description="Non-matching credentials")
     *     )
     *
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @api v1
     */
    public function login(Request $request): JsonResponse {
        $validated = $request->validate(['login' => ['required', 'max:255'], 'password' => ['required', 'min:8', 'max:255']]);

        if (LoginController::login($validated['login'], $validated['password'])) {
            $token = $request->user()->createToken('token', array_keys(AuthServiceProvider::$scopes));
            return $this->sendResponse(['token'      => $token->accessToken,
                                        'expires_at' => $token->token->expires_at->toIso8601String()])
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
