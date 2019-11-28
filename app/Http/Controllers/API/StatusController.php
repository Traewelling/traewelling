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
            return $this->sendError($validator->errors());
        }

        $statuses = StatusBackend::getGlobalDashboard();
        return response()->json($statuses);
    }

    public function show (Request $request, $id) {

    }

    public function update (Request $request, $id) {

    }

    public function destroy (Request $request, $id) {

    }
}
