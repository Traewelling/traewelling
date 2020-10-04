<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ResponseController as ResponseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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

    //login
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

    //logout
    public function logout(Request $request)
    {
        $isUser = $request->user()->token()->revoke();
        if($isUser){
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        }
        else{
            $error = "Something went wrong.";
            return $this->sendResponse($error);
        }


    }

    //getuser
    public function getUser(Request $request)
    {
        //$id = $request->user()->id;
        $user = $request->user();
        if($user){
            return $this->sendResponse($user);
        }
        else{
            $error = "user not found";
            return $this->sendResponse($error);
        }
    }
}
