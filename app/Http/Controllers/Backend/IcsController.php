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
use Throwable;

abstract class IcsController extends Controller
{
    public static function generateIcsCalendar(
        User    $user,
        string  $token,
        int     $limit = 10000,
        ?Carbon $from = null,
        ?Carbon $until = null,
        bool    $useEmojis = true,
        bool    $useRealTime = false,
    ): Calendar {
        $icsToken = IcsToken::where([['token', $token], ['user_id', $user->id]])->firstOrFail();

        $trainCheckIns = TrainCheckin::where('user_id', $user->id)
            //I don't know why, but the "with" eager loading doesn't work in prod. "HafasTrip" is always null then
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
            try {
                $name = '';
                if ($useEmojis) {
                    $name .= $checkIn?->HafasTrip?->category?->getEmoji() . ' ';
                }
                $name .= __(
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
                              ->startsAt($useRealTime ? $checkIn->origin_stopover->departure : $checkIn->origin_stopover->departure_planned)
                              ->endsAt($useRealTime ? $checkIn->destination_stopover->arrival : $checkIn->destination_stopover->arrival_planned);
                $calendar->event($event);
            } catch (Throwable $throwable) {
                report($throwable);
            }
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
