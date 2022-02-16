<?php

namespace App\Http\Controllers\API\v1;

use App\Enum\StatusVisibility;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\PrivacyPolicyController as PrivacyBackend;
use App\Http\Resources\PrivacyPolicyResource;

class PrivacyPolicyController extends ResponseController
{
    public function getPrivacyPolicy() {
        return new PrivacyPolicyResource(PrivacyBackend::getCurrentPrivacyPolicy());
    }


}
