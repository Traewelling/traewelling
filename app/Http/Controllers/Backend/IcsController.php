<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\IcsToken;
use App\Models\Checkin;
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

        $checkinQuery = Checkin::with(['originStation', 'destinationStation', 'Trip.stopovers'])
                               ->where('user_id', $user->id)
                               ->orderByDesc('departure')
                               ->limit($limit);

        if ($from !== null) {
            $checkinQuery->where('departure', '>=', $from);
        }
        if ($until !== null) {
            $checkinQuery->where('departure', '<=', $until);
        }

        $calendar = Calendar::create()
                            ->name(__('profile.last-journeys-of') . ' ' . $user->name)
                            ->description(__('ics.description', [], $user->language));

        $checkinQuery->chunk(1000, function($checkins) use ($calendar, $useRealTime, $user, $useEmojis) {
            foreach ($checkins as $checkin) {
                try {
                    $name = '';
                    if ($useEmojis) {
                        $name .= $checkin?->trip?->category?->getEmoji() . ' ';
                    }
                    $name .= __(
                        key:     'export.journey-from-to',
                        replace: [
                                     'origin'      => $checkin->originStation->name,
                                     'destination' => $checkin->destinationStation->name
                                 ],
                        locale:  $user->language
                    );

                    $event = Event::create()
                                  ->name($name)
                                  ->uniqueIdentifier($checkin->id)
                                  ->createdAt($checkin->created_at)
                                  ->startsAt($useRealTime ? $checkin->originStopover->departure : $checkin->originStopover->departure_planned)
                                  ->endsAt($useRealTime ? $checkin->destinationStopover->arrival : $checkin->destinationStopover->arrival_planned);
                    $calendar->event($event);
                } catch (Throwable $throwable) {
                    report($throwable);
                }
            }
        });

        $icsToken->update(['last_accessed' => now()]);
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
