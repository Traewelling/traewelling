<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Backend\WebFingerController as WebFingerBackend;

class WebFingerController extends Controller
{

    public function endpoint(Request $request): JsonResponse {
        if (!config('trwl.webfinger_active')) {
            return new JsonResponse(['message' => 'WebFinger is disabled. Contact the server administrator if you believe this is an error.'], 403);
        }
        $validated = $request->validate(['resource' => 'required']);
        $webFinger = new WebFingerBackend($validated['resource']);
        return $webFinger->renderResponse();
    }
}
