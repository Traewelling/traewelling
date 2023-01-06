<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyAcceptedException;
use App\Http\Controllers\Backend\PrivacyPolicyController as PrivacyBackend;
use App\Http\Resources\PrivacyPolicyResource;
use Illuminate\Http\JsonResponse;

class PrivacyPolicyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/static/privacy",
     *     tags={"Settings"},
     *     summary="Get the current privacy policy",
     *     description="Get the current privacy policy",
     *     @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="validFrom", example="2022-01-05T16:26:14.000000Z"),
     *                  @OA\Property(property="en", example="This is the english privacy policy"),
     *                  @OA\Property(property="de", example="Dies ist die deutsche Datenschutzerklärung"),
     *              )
     *         )
     *     )
     * )
     *
     * @return PrivacyPolicyResource
     */
    public function getPrivacyPolicy(): PrivacyPolicyResource {
        return new PrivacyPolicyResource(PrivacyBackend::getCurrentPrivacyPolicy());
    }

    /**
     * @OA\Post(
     *     path="/settings/acceptPrivacy",
     *     tags={"Settings"},
     *     summary="Accept the current privacy policy",
     *     description="Accept the current privacy policy",
     *     @OA\Response(response=204, description="Success"),
     *     @OA\Response(response=400, description="Already accepted"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={
     *           {"token": {}},
     *           {}
     *     }
     * )
     * @return JsonResponse
     */
    public function acceptPrivacyPolicy(): JsonResponse {
        try {
            PrivacyBackend::acceptPrivacyPolicy(user: auth()->user());
        } catch (AlreadyAcceptedException $exception) {
            $error = strtr("User already accepted privacy policy (valid from ptime) at utime", [
                'ptime' => $exception->getPrivacyValidity(),
                'utime' => $exception->getUserAccepted()
            ]);
            return $this->sendError(error: $error, code: 409);
        }

        return $this->sendResponse(code: 204);
    }
}
