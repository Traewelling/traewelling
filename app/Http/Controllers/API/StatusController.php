<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\StatusController as StatusBackend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;


class StatusController extends ResponseController
{
    public function index (Request $request) {
        $validator = Validator::make($request->all(), [
            'aroundDate' => 'date',
            'age' => 'in:older,newer',
            'maxStatuses' => 'integer',
            'user' => 'integer'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 400);
        }

        $statuses = StatusBackend::getGlobalDashboard();
        return response()->json($statuses);
    }

    public function show (Request $request, $id) {
        $StatusResponse = StatusBackend::getStatus($id);
        return sendResponse($StatusResponse);
    }

    public function update (Request $request, $id) {
        $this->validate($request, [
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
            return $this->sendError(__('controller.status.not-permitted'), 403)
        }
        return $this->sendResponse(['newBody' => $EditStatusResponse]);
    }

    public function destroy (Request $request, $id) {
        $DeleteStatusResponse = StatusBackend::DeleteStatus(Auth::user(), $id);

        if ($DeleteStatusResponse === null) {
            return $this->sendError('Not found');
        }
        if ($DeleteStatusResponse === false) {
            return $this->sendError(__('controller.status.not-permitted'), 403)
        }
        return $this->sendResponse(__('controller.status.delete-ok'));
    }
}
