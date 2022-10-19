<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\Auth\LoginController;
use App\Http\Resources\UserSettingsResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ResponseController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @api  v1
     */
    public function register(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'unique:users','max:25', 'regex:/^[a-zA-Z0-9_]*$/'],
            'name'     => ['required', 'max:50'],
            'email'    => ['required', 'email', 'unique:users'],
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
            $userToken = $user->createToken('token');
            return $this->sendResponse([
                                           'token'      => $userToken->accessToken,
                                           'message'    => 'Registration successful.',
                                           'expires_at' => $userToken->token->expires_at->toIso8601String()
                                       ]);
        }
        return $this->sendv1Error("Sorry! Registration is not successful.", 401);
    }

    /**
     *  @OA\Post(
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
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property (
     *                      property="token",
     *                      description="Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)",
     *                      example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZWU2ZWZiOWUxYTIwN2FmMjZjNjk4NjVkOTA5ODNmNzFjYzYyMzE5ODA3NGU1NjlhNjU1MGRiMTdhMWY5YmNhMmY4ZjNjNTQ4ZGZkZTY5ZmUiLCJpYXQiOjE2NjYxODUzMDYuOTczODU3LCJuYmYiOjE2NjYxODUzMDYuOTczODYsImV4cCI6MTY5NzcyMTMwNi45NDYyNDgsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.tiv8VeL8qw6BRwo5QZZ71Zn3WnFJjtvVciahiUJjzVNfqgofdRF6EoWrTFc_WmrgbVCdfXBjBI02fjbSrsD4.....",
     *                  ),
     *                  @OA\Property (
     *                      property="expires_at",
     *                      description="end of life for this token. Lifespan is usually one year.",
     *                      example="2023-10-19T15:15:06+02:00"
     *                  )
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
            $token = $request->user()->createToken('token');
            return $this->sendv1Response(['token'      => $token->accessToken,
                                          'expires_at' => $token->token->expires_at->toIso8601String()])
                        ->header('Authorization', $token->accessToken);
        }
        return $this->sendError('Non-matching credentials', 401);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @api v1
     */
    public function logout(Request $request): JsonResponse {
        $isUser = $request->user()->token()->revoke();
        if ($isUser) {
            return $this->sendv1Response();
        } else {
            return $this->sendv1Response("unknown", 500);
        }
    }

    /**
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @api v1
     */
    public function user(Request $request): JsonResponse {
        return $this->sendv1Response(new UserSettingsResource($request->user()));
    }

    public function refresh(Request $request): JsonResponse {
        $oldToken = $request->user()->token();
        $newToken = $request->user()->createToken('token');
        $oldToken->revoke();
        return $this->sendv1Response(['token'      => $newToken->accessToken,
                                      'expires_at' => $newToken->token->expires_at->toIso8601String()])
                    ->header('Authorization', $newToken->accessToken);
    }
}
