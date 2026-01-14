<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\IcsTokenController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\IcsExportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IcsController extends Controller
{
    public function renderIcs(Request $request): ?Response
    {
        $validated = $request->validate([
            'user_id' => ['required', 'numeric'],
            'token' => ['required', 'string'],
            'limit' => ['nullable', 'numeric', 'gte:1', 'lte:10000'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'emojis' => ['nullable', 'boolean'],
            'realtime' => ['nullable', 'boolean'],
        ]);

        $user = User::where('id', $validated['user_id'])->firstOrFail();

        try {
            $service = new IcsExportService($user, $validated['emojis'] ?? true, $validated['realtime'] ?? true);
            $calendar = $service->generateIcsCalendar(
                token: $validated['token'],
                limit: $validated['limit'] ?? 1000,
                from: isset($validated['from']) ? Carbon::parse($validated['from']) : null,
                until: isset($validated['until']) ? Carbon::parse($validated['until']) : null,
            );

            return response($calendar->get())
                ->header('Content-Type', 'text/calendar')
                ->header('charset', 'utf-8');
        } catch (ModelNotFoundException) {
            abort(403);
        }
    }

    public function createIcsToken(Request $request): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'max:255']]);

        $icsToken = IcsTokenController::createIcsToken(user: auth()->user(), name: $validated['name']);

        return redirect()->route('settings.ics')
            ->with('ics-success', strtr(__('settings.create-ics-token-success'), [
                ':link' => route('ics', [
                    'user_id' => $icsToken->user_id,
                    'token' => $icsToken->token,
                    'limit' => 10000,
                    'from' => '2010-01-01',
                    'until' => '2030-12-31',
                    'emojis' => true,
                    'realtime' => true,
                ]),
            ]));
    }

    public function revokeIcsToken(Request $request): RedirectResponse
    {
        $validated = $request->validate(['id' => ['required', 'exists:ics_tokens,id']]);

        try {
            IcsTokenController::revokeIcsToken(user: auth()->user(), tokenId: $validated['id']);

            return back()->with('success', __('settings.revoke-ics-token-success'));
        } catch (ModelNotFoundException) {
            return back()->with('error', __('messages.exception.general'));
        }
    }
}
