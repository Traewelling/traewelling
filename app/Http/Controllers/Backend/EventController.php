<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EventSuggestion;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

abstract class EventController extends Controller
{
    public static function suggestEvent(
        User    $user,
        string  $name,
        Carbon  $begin,
        Carbon  $end,
        Station $station = null,
        string  $url = null,
        string  $host = null,
        string  $hashtag = null,
    ): EventSuggestion {

        if ($hashtag != null && str_starts_with($hashtag, '#')) {
            $hashtag = substr($hashtag, 1);
        }

        $eventSuggestion = EventSuggestion::create([
                                                       'user_id'    => $user->id,
                                                       'name'       => $name,
                                                       'begin'      => $begin->toIso8601String(),
                                                       'end'        => $end->toIso8601String(),
                                                       'station_id' => $station?->id,
                                                       'url'        => $url,
                                                       'host'       => $host,
                                                       'hashtag'    => $hashtag,
                                                   ]);

        try {
            if (config('app.admin.notification.url') !== null) {
                Http::post(config('app.admin.notification.url'), [
                    'chat_id'    => config('app.admin.notification.chat_id'),
                    'text'       => strtr("<b>New event suggestion:</b>" . PHP_EOL .
                                          "Title: :name" . PHP_EOL .
                                          "Begin: :begin" . PHP_EOL .
                                          "End: :end" . PHP_EOL .
                                          "Suggested by user: :username", [
                                              ':name'     => $eventSuggestion->name,
                                              ':host'     => $eventSuggestion->host,
                                              ':begin'    => $eventSuggestion->begin->format('d.m.Y'),
                                              ':end'      => $eventSuggestion->end->format('d.m.Y'),
                                              ':username' => $eventSuggestion->user->username,
                                          ]),
                    'parse_mode' => 'HTML',
                ]);
            }
        } catch (Exception $e) {
            report($e);
        }

        return $eventSuggestion;
    }
}
