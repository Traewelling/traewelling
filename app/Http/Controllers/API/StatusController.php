<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class StatusController extends ResponseController
{

    public function enRoute () {
        $ActiveStatusesResponse = StatusBackend::getActiveStatuses();
        $response = [];
        if ($ActiveStatusesResponse['statuses'] !== null) {
            $response = ['statuses' => $ActiveStatusesResponse['statuses'], 'polylines' => $ActiveStatusesResponse['polylines']];
        }
        return $this->sendResponse($response);
    }

    public function getByEvent($id) {
        $EventStatusResponse = StatusBackend::getStatusesByEvent($id);
        return $this->sendResponse($EventStatusResponse);
    }

    public function index (Request $request) {
        $validator = Validator::make($request->all(), [
            'maxStatuses' => 'integer',
            'username' => 'string|required_if:view,user',
            'view' => 'in:user,global,personal'
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

    public function show ($id) {
        $StatusResponse = StatusBackend::getStatus($id);
        return $this->sendResponse($StatusResponse);
    }

    public function update (Request $request) {
        $validator = Validator::make($request->all(), [
            'body' => 'max:280',
            'business' => 'integer',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors(), 400);
        }
        $EditStatusResponse = StatusBackend::EditStatus(
            Auth::user(),
            $request['statusId'],
            $request['body'],
            $request['businessCheck']
        );
        if ($EditStatusResponse === null) {
            return $this->sendError('Not found');
        }
        if ($EditStatusResponse === false) {
            return $this->sendError(__('controller.status.not-permitted'), 403);
        }
        return $this->sendResponse(['newBody' => $EditStatusResponse]);
    }

    public function destroy ($id) {
        $DeleteStatusResponse = StatusBackend::DeleteStatus(Auth::user(), $id);

        if ($DeleteStatusResponse === null) {
            return $this->sendError('Not found');
        }
        if ($DeleteStatusResponse === false) {
            return $this->sendError(__('controller.status.not-permitted'), 403);
        }
        return $this->sendResponse(__('controller.status.delete-ok'));
    }
}
