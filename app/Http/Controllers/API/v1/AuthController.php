<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController as ResponseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Psy\Util\Json;

class AuthController extends ResponseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @api v1
     * @todo refactor this
     */
    public function register(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'unique:users'],
            'name'     => ['required'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                                        'status' => 'error',
                                        'errors' => $validator->errors()
                                    ], 422);
        }
        $input             = $request->only('username', 'name', 'email', 'password');
        $input['password'] = Hash::make($input['password']);
        $user              = User::create($input);

        if ($user) {
            $userToken = $user->createToken('token');
            return $this->sendResponse([
                                           'token'      => $userToken->accessToken,
                                           'message'    => 'Registration successful.',
                                           'expires_at' => $userToken->token->expires_at->toIso8601String()
                                       ]);
        }
        return $this->sendError("Sorry! Registration is not successful.", 401);
    }

    /**
     * Login
     * @param Request $request
     * @return JsonResponse
     * @api v1
     */
    public function login(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $token = $request->user()->createToken('token');
            return response()->json([
                                        'token'      => $token->accessToken,
                                        'expires_at' => $token->token->expires_at->toIso8601String()
                                    ], 200)
                             ->header('Authorization', $token->accessToken);
        }
        return response()->json(['error' => 'login_error'], 401);
    }

    /**
     * @param Request $request
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
     * @return JsonResponse
     * @api v1
     */
    public function user(Request $request): JsonResponse {
        $user = $request->user();
        return $this->sendResponse(new UserResource($user));
    }

    public function refresh(): JsonResponse {
        if ($token = Auth::guard()->refresh()) {
            return response()
                ->json(['status' => 'successs'], 200)
                ->header('Authorization', $token);
        }
        return response()->json(['error' => 'refresh_token_error'], 401);
    }
}
