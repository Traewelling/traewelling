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

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @deprecated with apiv1
     */
    public function logout(Request $request): JsonResponse {
        $isUser = $request->user()->token()->revoke();
        if ($isUser) {
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        }
        return $this->sendResponse('Something went wrong.');
    }

    public function getUser(Request $request): JsonResponse {
        $user = $request->user();
        if ($user) {
            return $this->sendResponse($user);
        }
        return $this->sendResponse('user not found');

    }
}
