<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlertRequest;
use App\Models\Alert;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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

    public function store(StoreAlertRequest $request): RedirectResponse
    {
        $alert = new Alert();
        $this->updateOrCreate($request, $alert);

        return redirect()
            ->route('admin.alerts')
            ->with('success', __('Alert created successfully.'));
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

    public function update(StoreAlertRequest $request, string $id): RedirectResponse
    {
        $alert = Alert::findOrFail($id);
        $this->updateOrCreate($request, $alert);

        return redirect()
            ->route('admin.alerts')
            ->with('success', __('Alert updated successfully.'));
    }

    public function destroy(Alert $alert): RedirectResponse
    {
        $alert->delete();

        return redirect()
            ->route('admin.alerts')
            ->with('success', __('Alert deleted successfully.'));
    }

    private function updateOrCreate(StoreAlertRequest $request, Alert $alert): void
    {
        DB::beginTransaction();
        $alert->type = $request->type;
        $alert->active_from = $request->active_from;
        $alert->active_until = $request->active_until;
        $alert->url = $request->url;
        $alert->save();

        $alert->translations()->updateOrCreate(
            ['locale' => 'de'],
            [
                'title' => $request->title_de,
                'content' => $request->content_de,
                'url' => $request->url_de,
            ]
        );

        $alert->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'title' => $request->title_en,
                'content' => $request->content_en,
                'url' => $request->url_en,
            ]
        );

        DB::commit();
    }
}
