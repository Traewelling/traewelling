<?php

namespace App\Http\Controllers\API;

use App\Enum\Business;
use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\Backend\User\DashboardController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\Status;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * @deprecated Will be replaced by APIv1
 */
class StatusController extends ResponseController
{

    public function index(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'maxStatuses' => 'integer',
            'username'    => 'string|required_if:view,user',
            'view'        => 'in:user,global,personal'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $view = 'global';
        if (!empty($request->view)) {
            $view = $request->view;
        }
        $statuses = ['statuses' => ''];
        if ($view === 'global') {
            $statuses['statuses'] = DashboardController::getGlobalDashboard(Auth::user());
        }
        if ($view === 'personal') {
            $statuses['statuses'] = DashboardController::getPrivateDashboard(Auth::user());
        }
        if ($view === 'user') {
            $statuses = UserBackend::getProfilePage($request->username);
        }
        return response()->json($statuses['statuses']);
    }
}
