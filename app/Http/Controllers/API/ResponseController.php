<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @deprecated Will be replaced by APIv1
 */
class ResponseController extends Controller
{
    public function sendResponse($response): JsonResponse {
        $disclaimer = 'APIv0 is deprecated and will be removed in the future __WITHOUT ANY OFFICIAL NOTICE__. Please use APIv1 instead.';
        if (is_array($response)) {
            $response = array_merge(['disclaimer' => $disclaimer], $response);
        }
        return response()->json($response);
    }

    public function sendv1Response(
        array|string|object $data = null,
        int                 $code = 200,
        array               $additional = null
    ): JsonResponse {
        if ($data === null) {
            return response()->json(
                data:   ['status' => 'success'],
                status: $code
            );
        }
        $response = [
            'disclaimer' => 'APIv1 is not officially released for use and is also not fully documented. Use at your own risk. Data fields may change at any time without notice.',
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

    public function sendError(array|string $error, int $code = 404): JsonResponse {
        $response = [
            'error' => $error,
        ];
        return response()->json($response, $code);
    }
}
