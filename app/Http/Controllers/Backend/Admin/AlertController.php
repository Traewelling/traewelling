<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Contracts\View\View;

class AlertController extends Controller
{
    public function index(): View
    {
        $alerts = Alert::with('translations')
            ->orderByDesc('active_from')
            ->orderByDesc('active_until')
            ->paginate(50);

        return view('admin.alerts.index', [
            'alerts' => $alerts,
        ]);
    }

    public function create(): View
    {
        return view('admin.alerts.show', [
            'alert' => null,
        ]);
    }

    public function edit(string $alertId): View
    {
        $alert = Alert::with('translations')
            ->where('id', $alertId)
            ->firstOrFail();

        return view('admin.alerts.show', [
            'alert' => $alert,
        ]);
    }
}
