<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;

class ResponseController extends Controller
{
    /**
     * Returns Responses as correctly formatted json objects.
     * @hideFromAPIDocumentation
     * @param $response
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($response) {
        return response()->json($response, 200);
    }

    /**
     * Takes a Response and an error code and returns a valid json object with corresponding Statuscode
     * @hideFromAPIDocumentation
     * @param $error
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $code = 404) {
        $response = [
            'error' => $error,
        ];
        return response()->json($response, $code);
    }
}
