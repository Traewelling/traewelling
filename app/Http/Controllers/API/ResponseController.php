<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;

class ResponseController extends Controller
{
    public function sendResponse($response) {
        return response()->json($response, 200);
    }

    public function sendv1Response($response, $code=200) {
        return response()->json(["data" => $response], $code);
    }

    public function sendError($error, $code = 404) {
        $response = [
            'error' => $error,
        ];
        return response()->json($response, $code);
    }
}
