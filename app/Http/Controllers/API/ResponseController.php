<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\JsonResponse;

class ResponseController extends Controller
{
    public function sendResponse($response) {
        return response()->json($response, 200);
    }

    public function sendv1Response($data = null, $code = 200): JsonResponse {
        if ($data === null) {
            return response()->json(["status" => "success"], $code);
        }
        return response()->json(["data" => $data], $code);
    }

    public function sendError($error, $code = 404) {
        $response = [
            'error' => $error,
        ];
        return response()->json($response, $code);
    }
}
