<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ResponseController extends Controller
{

    public function sendv1Response(
        array|string|object $data = null,
        int                 $code = 200,
        array               $additional = null
    ): JsonResponse {
        $disclaimer = 'APIv1 is not officially released for use and is also not fully documented. Use at your own risk. Data fields may change at any time without notice.';
        if ($data === null) {
            return response()->json(
                data:   [
                            'disclaimer' => $disclaimer,
                            'status'     => 'success',
                        ],
                status: $code
            );
        }
        $response = [
            'disclaimer' => $disclaimer,
            'data'       => $data,
        ];
        $response = $additional ? array_merge($response, $additional) : $response;
        return response()->json($response, $code);
    }

    public function sendv1Error(array|string $error, int $code = 404, array $additional = null): JsonResponse {
        $response = [
            'message' => $error,
        ];
        $response = $additional ? array_merge($response, ["meta" => $additional]) : $response;
        return response()->json($response, $code);
    }
}
