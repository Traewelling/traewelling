<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\ChangelogController as BackendController;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ChangelogController extends Controller
{
    private BackendController $backendController;

    public function __construct(BackendController $backendController)
    {
        $this->backendController = $backendController;
    }

    public function renderChangelog(): View
    {
        return view('changelog', [
            'changelog' => $this->backendController->getChangelog(),
        ]);
    }
}
