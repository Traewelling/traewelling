<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ChangelogService;
use Illuminate\View\View;

class ChangelogController extends Controller
{
    private ChangelogService $backendController;

    public function __construct(ChangelogService $backendController)
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
