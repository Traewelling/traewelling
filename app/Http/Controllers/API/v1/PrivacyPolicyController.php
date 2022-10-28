<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AlreadyAcceptedException;
use App\Http\Controllers\Backend\PrivacyPolicyController as PrivacyBackend;
use App\Http\Resources\PrivacyPolicyResource;
use Illuminate\Http\JsonResponse;

class PrivacyPolicyController extends Controller
{
    public function getPrivacyPolicy(): PrivacyPolicyResource {
        return new PrivacyPolicyResource(PrivacyBackend::getCurrentPrivacyPolicy());
    }

    public function acceptPrivacyPolicy(): JsonResponse {
        try {
            PrivacyBackend::acceptPrivacyPolicy(user: auth()->user());
        } catch (AlreadyAcceptedException $exception) {
            $error = strtr("User already accepted privacy policy (valid from ptime) at utime", [
                'ptime' => $exception->getPrivacyValidity(),
                'utime' => $exception->getUserAccepted()
            ]);
            return $this->sendv1Error(error: $error);
        }

        return $this->sendv1Response(code: 204);
    }
}
