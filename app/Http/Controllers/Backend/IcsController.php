<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\IcsToken;
use App\Models\TrainCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

abstract class IcsController extends Controller
{
    public static function generateIcsCalendar(
        User    $user,
        string  $token,
        int     $limit = 10000,
        ?Carbon $from = null,
        ?Carbon $until = null
    ): Calendar {
        $icsToken = IcsToken::where([['token', $token], ['user_id', $user->id]])->firstOrFail();

        $trainCheckIns = TrainCheckin::with(['HafasTrip'])
                                     ->where('user_id', $user->id)
                                     ->orderByDesc('departure')
                                     ->limit($limit);

        if ($from !== null) {
            $trainCheckIns->where('departure', '>=', $from->toIso8601String());
        }
        if ($until !== null) {
            $trainCheckIns->where('departure', '<=', $until->toIso8601String());
        }

        $calendar = Calendar::create()
                            ->name(__('profile.last-journeys-of') . ' ' . $user->name)
                            ->description(__('ics.description', [], $user->language));

        foreach ($trainCheckIns->get() as $checkIn) {
            $name = $checkIn?->HafasTrip?->category?->getEmoji();
            $name .= ' ' . __(
                    key:     'export.journey-from-to',
                    replace: [
                                 'origin'      => $checkIn->Origin->name,
                                 'destination' => $checkIn->Destination->name
                             ],
                    locale:  $user->language
                );

            $event = Event::create()
                          ->name($name)
                          ->uniqueIdentifier($checkIn->id)
                          ->createdAt($checkIn->created_at)
                          ->startsAt($checkIn->departure)
                          ->endsAt($checkIn->arrival);
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
