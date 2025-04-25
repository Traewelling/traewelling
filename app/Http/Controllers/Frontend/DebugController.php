<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\MotisSourceLicense;
use App\Repositories\OAuthClientRepository;
use App\Rules\SecureUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\ClientRepository;

/**
 * Frontend-Controller for some debug views - so user can see which data is used by current träwelling instance.
 */
class DebugController extends Controller
{

    public function showMotisSources(): View {
        return view('debug.motis-source', [
            'sources' => MotisSourceLicense::orderBy('country')->get()
        ]);
    }

}
