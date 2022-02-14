<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Admin\TelegramController;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

abstract class EventController extends Controller
{
    public static function suggestEvent(
        User $user,
        string $name,
        Carbon $begin,
        Carbon $end,
        string $url = null,
        string $host = null
    ): EventSuggestion {
        $eventSuggestion = EventSuggestion::create([
                                                       'user_id' => $user->id,
                                                       'name'    => $name,
                                                       'begin'   => $begin->toIso8601String(),
                                                       'end'     => $end->toIso8601String(),
                                                       'url'     => $url,
                                                       'host'    => $host
                                                   ]);


        TelegramController::sendAdminMessage(strtr("<b>Neuer Veranstaltungsvorschlag</b>" . PHP_EOL .
                                                   "Title: :name" . PHP_EOL .
                                                   "Veranstalter: :host" . PHP_EOL .
                                                   "Beginn: :begin" . PHP_EOL .
                                                   "Ende: :end" . PHP_EOL .
                                                   "Benutzer: :username\n" . PHP_EOL .
                                                   "Der Vorschlag kann im <a href=\"" .
                                                   route('admin.events.suggestions') .
                                                   "\">Adminpanel</a> bearbeitet werden.", [
                                                       ':name'     => $eventSuggestion->name,
                                                       ':host'     => $eventSuggestion->host,
                                                       ':begin'    => $eventSuggestion->begin->format('d.m.Y'),
                                                       ':end'      => $eventSuggestion->end->format('d.m.Y'),
                                                       ':username' => $eventSuggestion->user->username,
                                                   ]));

        return $eventSuggestion;
    }

    public static function activeEvents(): ?Collection {
        $now = Carbon::now();

        return Event::where([
                                ['begin', '<=', $now],
                                ['end', '>=', $now]
                            ])->get();
    }

    public static function getBySlug(string $slug): ?Event {
        return Event::where('slug', '=', $slug)->firstOrFail();
    }

    public static function getUpcomingEvents(): Paginator {
        return Event::where('end', '>=', Carbon::now()->toIso8601String())
                                      ->orderBy('begin')
                                      ->simplepaginate(15);
    }

}
