<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Backend\Auth\LoginController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @deprecated Will be replaced by APIv1
 */
class AuthController extends ResponseController
{
    //create user
    public function signup(Request $request) {
        $validator = Validator::make($request->all(), [
            'username'         => 'required|string|unique:users',
            'name'             => 'required|string|',
            'email'            => 'required|string|email|unique:users',
            'password'         => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $input             = $request->all();
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

        $error = "Sorry! Registration is not successful.";
        return $this->sendError($error, 401);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @deprecated with apiv1
     */
    public function login(Request $request) {
        $validator = $request->validate(['email' => ['required', 'max:255'], 'password' => ['required', 'min:8']]);

        if (!LoginController::login($validator['email'], $validator['password'])) {
            $error = "Unauthorized";
            return $this->sendError($error, 401);
        }
        $userToken = $request->user()->createToken('token');

        return $this->sendResponse([
                                       'token'      => $userToken->accessToken,
                                       'expires_at' => $userToken->token->expires_at->toIso8601String()
                                   ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @deprecated with apiv1
     */
    public function logout(Request $request) {
        $isUser = $request->user()->token()->revoke();
        if ($isUser) {
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        } else {
            $error = "Something went wrong.";
            return $this->sendResponse($error);
        }


    }

    //getuser
    public function getUser(Request $request) {
        //$id = $request->user()->id;
        $user = $request->user();
        if ($user) {
            return $this->sendResponse($user);
        } else {
            $error = "user not found";
            return $this->sendResponse($error);
        }
    }
}
