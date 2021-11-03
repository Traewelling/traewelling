<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\IcsToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class IcsController extends Controller
{
    public static function generateIcsCalendar(
        User    $user,
        string  $token,
        int     $limit = 10000,
        ?Carbon $from = null,
        ?Carbon $until = null): Calendar {
        $icsToken = IcsToken::where([['token', $token], ['user_id', $user->id]])->firstOrFail();

        $trainCheckIns = $user->statuses->map(function($status) {
            return $status->trainCheckIn;
        });

        if (isset($from)) {
            $trainCheckIns = $trainCheckIns->filter(function($checkIn) use ($from) {
                return $checkIn->departure->isAfter($from);
            });
        }

        if (isset($until)) {
            $trainCheckIns = $trainCheckIns->filter(function($checkIn) use ($until) {
                return $checkIn->departure->isBefore($until);
            });
        }

        $trainCheckIns = $trainCheckIns->sortByDesc('created_at')
                                       ->take($limit);

        $calendar = Calendar::create()
                            ->name(__('profile.last-journeys-of') . ' ' . $user->name)
                            ->description('Check-Ins at traewelling.de');

        foreach ($trainCheckIns as $checkIn) {
            $event = Event::create()
                          ->name(__('export.journey-from-to', [
                              'origin'      => $checkIn->Origin->name,
                              'destination' => $checkIn->Destination->name
                          ]))
                          ->uniqueIdentifier($checkIn->id)
                          ->createdAt($checkIn->created_at)
                          ->startsAt($checkIn->origin_stopover->departure ?? $checkIn->departure)
                          ->endsAt($checkIn->destination_stopover->arrival ?? $checkIn->arrival);
            $calendar->event($event);
        }

        $icsToken->update(['last_accessed' => Carbon::now()]);

        return $calendar;
    }

    public static function createIcsToken(User $user, $name): IcsToken {
        return IcsToken::create([
                                    'user_id' => $user->id,
                                    'name'    => $name,
                                    'token'   => Str::uuid()->toString()
                                ]);
    }

    /**
     * @param User $user
     * @param int  $tokenId
     */
    public static function revokeIcsToken(User $user, int $tokenId): void {
        $affectedRows = IcsToken::where('user_id', $user->id)
                                ->where('id', $tokenId)
                                ->delete();

        if ($affectedRows === 0) {
            throw new ModelNotFoundException();
        }
    }
}
