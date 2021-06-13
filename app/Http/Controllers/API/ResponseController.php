<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\JsonResponse;

class ResponseController extends Controller
{
    public function sendResponse($response): JsonResponse {
        return response()->json($response, 200);
    }

    public function sendv1Response(
        array|string|object $data = null,
        int $code = 200,
        array $additional = null
    ): JsonResponse {
        if ($data === null) {
            return response()->json(["status" => "success"], $code);
        }
        $response = ["data" => $data];
        $response = $additional ? array_merge($response, $additional) : $response;
        return response()->json($response, $code);
    }

    public function sendError(array|string $error, int $code = 404): JsonResponse {
        $response = [
            'error' => $error,
        ];
        return response()->json($response, $code);
    }
}
