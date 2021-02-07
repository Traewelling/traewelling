<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ResponseController as ResponseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 *
 *
 *
 * Class AuthController
 * @package App\Http\Controllers\API
 */
class AuthController extends ResponseController
{
    /**
     * Sign-Up
     * This endpoint is meant for creating a new user with username & password.
     * You should probably start here.
     *
     * @group User management
     * @unauthenticated
     * @bodyParam username string required
     * The @-name of a user. Must be uniqe, max 15 chars and apply to regex:/^[a-zA-Z0-9_]*$/ Example: Gertrud123
     * @bodyParam name string required
     * The displayname of a user. Max 50 chars. Example: Gertrud
     * @bodyParam email string required
     * The mail of the user. Example: gertrud@traewelling.de
     * @bodyParam password string required
     * Example: thisisnotasecurepassword123
     * @bodyParam confirm_password string required
     * Must be equal to password. Example: thisisnotasecurepassword123
     * @response 200 {
     * "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3MzQ5MTQwMWZhNjRkMmU2MzgwNGQ4MWJhOTI0MjRlMmQ2ZmYyZjIyZjFiZmU1ZDUyOTExZjE0N2M4YWM5MTI3In0.eyJhdWQiOiIzIiwianRpIjoiZDFiMjNkYWU1NzRjOWE5OTczNDkxNDAxZmE2NGQyZTYzODA0ZDgxYmE5MjQyNGUyZDZmZjJmMjJmMWJmZTVkNTI5MTFmMTQ3YzhhYzkxMjciLCJpYXQiOjE1ODI5MDIyMDIsIm5iZiI6MTU4MjkwMjIwMiwiZXhwIjoxNjE0NTI0NjAyLCJzdWIiOiIxMCIsInNjb3BlcyI6W119.XWJcsbhgOQXqk-OrjKaRMRouo5AS4TkniyShH50O8K8KjaJYHP9Ltm3eMCpqarZpaBVucnsSKKimVVT9c1AD-Iq5n8AqZ3Mhgbh6Ik5-VqMAs89mLBwWj8seh_hgUmS0AqZMxUvkzZDpaU7Ub_EtoBUQ6l7up2tNXrA12mvg57LpvibWl6tXVLI2cBlEvNoTY3DPEjLFKMkdela7bhkoh4OAtJAnv1iNspuxcuhHp4PfgWlmaVGn4HdyfchNDJdSiWuiYy1LbRzpb9gdmmZtrDa--OfVRxodzE9sVIrLWXD_RRldejqyarbSke88ucMlALgCbBL88r00X2LEAXq565_s7ILbqEfVh1YN9ehfP8kCM9bf_Yop4G9QxgkO_xEhcv-Sj72rUph6TgS68QjEXculgizeVRTeCgW5X07UxCxy12jGuZMq3JjYU_kOmF1Sr79KSSZnFe27_f1kjbgEGSVwVKq_R4HcmM9ZGazpfbRPqaZnjUl3H5_0YAa7hZh0P1MYcJywx0tdY3inkZFBXhz1_3Xt6sULqlFRS4Lh0hP0o2E5jrCtVmeKGTgUvvbumEVyKpisjzpQK08i-rMSnYXSUbI6JNXc9z3PVgWzVt1lAdG66xNci7JQ3gdIoM4cQFBcGI8qQmfRMjvzXmmvoWY_hottmtOSK9AV_AP4zSw",
     * "expires_at": "2021-10-01T12:00:00+02:00",
     * "message": "Registration successfull.."
     * }
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied."
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signup(Request $request) {
        $validator = Validator::make($request->all(), [
            'username'         => 'required|string|unique:users|max:15|regex:/^[a-zA-Z0-9_]*$/',
            'name'             => 'required|string|max:50',
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
     * Login
     * This endpoint handles a normal user login
     *
     * @group User management
     * @bodyParam email string required
     * @bodyParam password string required
     *
     * @response 200 {
     * "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQxYjIzZGFlNTc0YzlhOTk3MzQ5MTQwMWZhNjRkMmU2MzgwNGQ4MWJhOTI0MjRlMmQ2ZmYyZjIyZjFiZmU1ZDUyOTExZjE0N2M4YWM5MTI3In0.eyJhdWQiOiIzIiwianRpIjoiZDFiMjNkYWU1NzRjOWE5OTczNDkxNDAxZmE2NGQyZTYzODA0ZDgxYmE5MjQyNGUyZDZmZjJmMjJmMWJmZTVkNTI5MTFmMTQ3YzhhYzkxMjciLCJpYXQiOjE1ODI5MDIyMDIsIm5iZiI6MTU4MjkwMjIwMiwiZXhwIjoxNjE0NTI0NjAyLCJzdWIiOiIxMCIsInNjb3BlcyI6W119.XWJcsbhgOQXqk-OrjKaRMRouo5AS4TkniyShH50O8K8KjaJYHP9Ltm3eMCpqarZpaBVucnsSKKimVVT9c1AD-Iq5n8AqZ3Mhgbh6Ik5-VqMAs89mLBwWj8seh_hgUmS0AqZMxUvkzZDpaU7Ub_EtoBUQ6l7up2tNXrA12mvg57LpvibWl6tXVLI2cBlEvNoTY3DPEjLFKMkdela7bhkoh4OAtJAnv1iNspuxcuhHp4PfgWlmaVGn4HdyfchNDJdSiWuiYy1LbRzpb9gdmmZtrDa--OfVRxodzE9sVIrLWXD_RRldejqyarbSke88ucMlALgCbBL88r00X2LEAXq565_s7ILbqEfVh1YN9ehfP8kCM9bf_Yop4G9QxgkO_xEhcv-Sj72rUph6TgS68QjEXculgizeVRTeCgW5X07UxCxy12jGuZMq3JjYU_kOmF1Sr79KSSZnFe27_f1kjbgEGSVwVKq_R4HcmM9ZGazpfbRPqaZnjUl3H5_0YAa7hZh0P1MYcJywx0tdY3inkZFBXhz1_3Xt6sULqlFRS4Lh0hP0o2E5jrCtVmeKGTgUvvbumEVyKpisjzpQK08i-rMSnYXSUbI6JNXc9z3PVgWzVt1lAdG66xNci7JQ3gdIoM4cQFBcGI8qQmfRMjvzXmmvoWY_hottmtOSK9AV_AP4zSw",
     * "expires_at": "2021-10-01T12:00:00+02:00"
     * }
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
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
     * Logout
     * This terminates the session and invalidates the bearer token
     *
     * @group User management
     * @response 200 {
     * "message": "Successfully logged out."
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
