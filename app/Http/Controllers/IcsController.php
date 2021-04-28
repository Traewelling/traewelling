<?php

namespace App\Http\Controllers;

use App\Models\IcsToken;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class IcsController extends Controller
{
    public function renderIcs(Request $request): Response {
        $validated = $request->validate([
                                            'user_id' => ['required', 'exists:users,id'],
                                            'token'   => ['required', 'exists:ics_tokens,token'],
                                            'limit'   => ['nullable', 'numeric', 'gte:1', 'lte:10000'],
                                            'from'    => ['nullable', 'date'],
                                            'until'   => ['nullable', 'date']
                                        ]);

        $icsToken = IcsToken::where('token', $validated['token'])->first();

        if ($icsToken->user_id != $validated['user_id']) {
            abort(404);
        }

        if (!isset($validated['limit'])) {
            $validated['limit'] = 10000;
        }

        $user = $icsToken->user;
        $user->load([]);

        $trainCheckIns = $user->statuses
            ->map(function($status) {
                return $status->trainCheckIn;
            });

        if (isset($validated['from'])) {
            $from          = Carbon::parse($validated['from']);
            $trainCheckIns = $trainCheckIns->filter(function($checkIn) use ($from) {
                return $checkIn->departure->isAfter($from);
            });
        }

        if (isset($validated['until'])) {
            $until         = Carbon::parse($validated['until']);
            $trainCheckIns = $trainCheckIns->filter(function($checkIn) use ($until) {
                return $checkIn->departure->isBefore($until);
            });
        }

        $trainCheckIns = $trainCheckIns->sortByDesc('created_at')
                                       ->take($validated['limit']);

        $calendar = Calendar::create()
                            ->name(__('profile.last-journeys-of') . ' ' . $user->name)
                            ->description('Check-Ins at traewelling.de');

        foreach ($trainCheckIns as $checkIn) {
            $event = Event::create()
                          ->name(strtr(__('export.journey-from-to'), [
                              ':origin'      => $checkIn->Origin->name,
                              ':destination' => $checkIn->Destination->name
                          ]))
                          ->uniqueIdentifier($checkIn->id)
                          ->createdAt($checkIn->created_at)
                          ->startsAt($checkIn->origin_stopover->departure ?? $checkIn->departure)
                          ->endsAt($checkIn->destination_stopover->arrival ?? $checkIn->arrival);
            $calendar->event($event);
        }

        $icsToken->update(['last_accessed' => Carbon::now()]);

        return response($calendar->get())
            ->header('Content-Type', 'text/calendar')
            ->header('charset', 'utf-8');
    }

    public function createIcsToken(Request $request): RedirectResponse {
        $validated = $request->validate(['name' => ['required', 'max:255']]);

        $icsToken = IcsToken::create([
                                         'user_id' => auth()->user()->id,
                                         'name'    => $validated['name'],
                                         'token'   => Str::uuid()->toString()
                                     ]);

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
        $validated = $request->validate([
                                            'id' => ['required', 'exists:ics_tokens,id']
                                        ]);

        $affectedRows = IcsToken::where('user_id', auth()->user()->id)
                                ->where('id', $validated['id'])
                                ->delete();

        if ($affectedRows == 0) {
            return back()->with('error', __('messages.exception.general'));
        }

        return back()->with('success', __('settings.revoke-ics-token-success'));
    }
}
