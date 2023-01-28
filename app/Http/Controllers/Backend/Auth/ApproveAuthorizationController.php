<?php

namespace App\Http\Controllers\Backend\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController as PassportApproveAuthorizationController;

class ApproveAuthorizationController extends PassportApproveAuthorizationController
{
    public function approve(Request $request)
    {
        $webhook = $request->session()->get('webhook');
        $response = parent::approve($request);
        parse_str(parse_url($response->headers->get("Location"))["query"], $query);
        $code = $query['code'];
        return $response;
    }
}
