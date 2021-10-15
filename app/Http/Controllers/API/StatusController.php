<?php

namespace App\Http\Controllers\API;

use App\Exceptions\PermissionException;
use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\Status;
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

    public function enRoute() {
        $activeStatusesResponse = StatusBackend::getActiveStatuses();
        $response               = [];
        if ($activeStatusesResponse['statuses'] !== null) {
            $response = [
                'statuses'  => $activeStatusesResponse['statuses'],
                'polylines' => $activeStatusesResponse['polylines']
            ];
        }
        return $this->sendResponse($response);
    }

    public function getByEvent(int $eventID): JsonResponse {
        $eventStatusResponse = StatusBackend::getStatusesByEvent(null, $eventID);
        return $this->sendResponse($eventStatusResponse['statuses']);
    }

    public function index(Request $request) {
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
            $statuses['statuses'] = StatusBackend::getGlobalDashboard();
        }
        if ($view === 'personal') {
            $statuses['statuses'] = StatusBackend::getDashboard(Auth::user());
        }
        if ($view === 'user') {
            $statuses = UserBackend::getProfilePage($request->username);
        }
        return response()->json($statuses['statuses']);
    }

    public function show($statusId) {
        $statusResponse = StatusBackend::getStatus($statusId);
        return $this->sendResponse($statusResponse);
    }

    public function update(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'body'     => 'max:280',
            'business' => 'integer',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        try {
            $editStatusResponse = StatusBackend::EditStatus(
                user:       Auth::user(),
                statusId:   $request['statusId'],
                body:       $request['body'],
                business:   $request['businessCheck'],
                visibility: null
            );
        } catch (ModelNotFoundException) {
            return $this->sendError('Not found');
        } catch (PermissionException) {
            return $this->sendError(__('controller.status.not-permitted'), 403);
        }
        return $this->sendResponse(['newBody' => $editStatusResponse->body]);
    }

    public function destroy($statusId) {
        try {
            StatusBackend::DeleteStatus(Auth::user(), $statusId);
        } catch (ModelNotFoundException) {
            return $this->sendError('Not found');
        } catch (PermissionException) {
            return $this->sendError(__('controller.status.not-permitted'), 403);
        }

        return $this->sendResponse(__('controller.status.delete-ok'));
    }

    public function createLike($statusId) {
        $status = Status::find($statusId);
        if ($status == null) {
            return $this->sendError("false", 404);
        }
        try {
            StatusBackend::createLike(Auth::user(), $status);
            return $this->sendResponse(true);
        } catch (StatusAlreadyLikedException $e) {
            return $this->sendError("false", 400);
        }

    }

    public function destroyLike(int $statusId): JsonResponse {
        try {
            StatusBackend::destroyLike(Auth::user(), $statusId);
            return $this->sendResponse(true);
        } catch (InvalidArgumentException) {
            return $this->sendResponse(false);
        }
    }

    public function getLikes($statusId) {
        $getLikesResponse = StatusBackend::getLikes($statusId);

        return $this->sendResponse($getLikesResponse);
    }
}
