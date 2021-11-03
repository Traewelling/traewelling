<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\IcsController as BackendIcsController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IcsController extends Controller
{
    public function renderIcs(Request $request): ?Response {
        $validated = $request->validate([
                                            'user_id' => ['required', 'exists:users,id'],
                                            'token'   => ['required', 'exists:ics_tokens,token'],
                                            'limit'   => ['nullable', 'numeric', 'gte:1', 'lte:10000'],
                                            'from'    => ['nullable', 'date'],
                                            'until'   => ['nullable', 'date']
                                        ]);

        $user               = User::where('id', $validated['user_id'])->firstOrFail();
        $validated['limit'] = $validated['limit'] ?? 1000;
        $from               = Carbon::parse($validated['from']);
        $until              = Carbon::parse($validated['until']);

        try {
            $calendar = BackendIcsController::generateIcsCalendar(
                user:  $user,
                token: $validated['token'],
                limit: $validated['limit'],
                from:  $from,
                until: $until
            );
            return response($calendar->get())
                ->header('Content-Type', 'text/calendar')
                ->header('charset', 'utf-8');
        } catch (ModelNotFoundException) {
            return response(null, 404);
        }
    }

    public function createIcsToken(Request $request): RedirectResponse {
        $validated = $request->validate(['name' => ['required', 'max:255']]);

        $icsToken = BackendIcsController::createIcsToken(user: auth()->user(), name: $validated['name']);

        return back()->with('success', strtr(__('settings.create-ics-token-success'), [
            ':link' => route('ics', [
                'user_id' => $icsToken->user_id,
                'token'   => $icsToken->token,
                'limit'   => 10000,
                'from'    => '2010-01-01',
                'until'   => '2030-12-31'
            ])
        ]));
    }

    public function revokeIcsToken(Request $request): RedirectResponse {
        $validated = $request->validate(['id' => ['required', 'exists:ics_tokens,id']]);

        try {
            BackendIcsController::revokeIcsToken(user: auth()->user(), tokenId: $validated['id']);
            return back()->with('success', __('settings.revoke-ics-token-success'));
        } catch (ModelNotFoundException) {
            return back()->with('error', __('messages.exception.general'));
        }
    }
}
