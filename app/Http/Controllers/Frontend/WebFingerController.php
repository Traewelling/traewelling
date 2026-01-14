<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\WebFingerController as WebFingerBackend;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebFingerController extends Controller
{
    public function endpoint(Request $request): JsonResponse
    {
        if (!config('trwl.webfinger_active')) {
            return new JsonResponse(['message' => 'WebFinger is disabled. Contact the server administrator if you believe this is an error.'], 403);
        }
        $validated = $request->validate(['resource' => 'required']);
        $webFinger = new WebFingerBackend($validated['resource']);
        try {
            return $webFinger->renderResponse();
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }
    }
}
