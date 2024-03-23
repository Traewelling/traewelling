<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\PrivacyPolicyController;
use App\Jobs\MonitoredPersonalDataExportJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GdprExportController extends Controller
{

    public function export($userId=null) {
        $user = Auth::user();
        if ($userId !== null) {
            $user = User::findOrFail($userId);
        }
        dispatch(new MonitoredPersonalDataExportJob($user));
    }

}
