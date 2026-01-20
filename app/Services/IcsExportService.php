<?php

namespace App\Services;

use App\Dto\Internal\IcsExportStatus;
use App\Dto\Internal\IcsExportStatusTag;
use App\Helpers\CacheKey;
use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\IcsToken;
use App\Models\User;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Throwable;

class IcsExportService extends Controller
{
    private User $user;

    private bool $useEmojis;

    private bool $useRealTime;

    public function __construct(User $user, bool $useEmojis = true, bool $useRealTime = false)
    {
        $this->user = $user;
        $this->useEmojis = $useEmojis;
        $this->useRealTime = $useRealTime;
    }

    public function generateIcsCalendar(
        string $token,
        int $limit = 10000,
        ?Carbon $from = null,
        ?Carbon $until = null,
    ): Calendar {
        $icsToken = IcsToken::where([['token', $token], ['user_id', $this->user->id]])->firstOrFail();

        $calendar = Calendar::create()
            ->name(__('profile.last-journeys-of') . ' ' . $this->user->name)
            ->description(__('ics.description', [], $this->user->language));

        $from = $from ?? Carbon::now()->subMonths(3);
        $until = $until ?? Carbon::now()->addMonths(2);

        Log::debug('Starting ICS export', [
            'user_id' => $this->user->id,
            'from' => $from->toDateTimeString(),
            'until' => $until->toDateTimeString(),
            'limit' => $limit,
        ]);

        $date = $from->copy();
        while ($date->lessThanOrEqualTo($until)) {
            $events = $this->getCachedCheckinsForMonth($date, $limit);
            Log::debug('Adding events for month', [
                'user_id' => $this->user->id,
                'date' => $date->toDateString(),
                'count' => count($events),
            ]);
            foreach ($events as $event) {
                $calendar->event($this->getEvent($event));
            }
            $date->addMonth();
        }

        $icsToken->update(['last_accessed' => now()]);

        return $calendar;
    }

    /** @return IcsExportStatus[] */
    private function getCachedCheckinsForMonth(Carbon $date, int $limit): array
    {
        $date = $date->copy();
        $cacheKey = CacheKey::getIcsUserMonthlyKey($this->user, $date);
        $calculatingKey = CacheKey::getIcsUserMonthlyCalculatingKey($this->user, $date);
        $ttlKey = CacheKey::getIcsUserMonthlyTtlKey($this->user, $date);

        $stats = Cache::get($cacheKey, null);
        $calculating = Cache::get($calculatingKey, false);
        $ttl = Cache::get($ttlKey, 0);

        if (
            ($stats === null || $ttl < now()->format('u'))
            && !$calculating
        ) {
            Cache::put($calculatingKey, true, now()->addMinutes(15));
            dispatch(function () use ($cacheKey, $ttlKey, $date, $limit) {
                $stats = $this->getCheckinsForMonth($date, $limit);

                // ttl for this month and every future month should be 15 minutes
                // ttl for past months should be 3-6 days (randomizing to avoid cache stampedes)
                $ttl = $date->isBefore(now()->startOfMonth()) ? now()->addDays(rand(3, 6)) : now()->addMinutes(15);

                Log::debug('Caching checkins for month', [
                    'user_id' => $this->user->id,
                    'date' => $date->toDateString(),
                    'count' => count($stats),
                    'ttl' => $ttl,
                ]);
                Cache::put($cacheKey, $stats, now()->addDays(30));
                Cache::put($ttlKey, $ttl);
            })->afterResponse();
        }

        return $stats ?? [];
    }

    private function getEvent(IcsExportStatus $checkin): Event
    {
        $name = '';
        if ($checkin->emoji) {
            $name .= $checkin->emoji . ' ';
        }
        $name .= __(
            key: 'export.journey-from-to',
            replace: [
                'origin' => $checkin->originName,
                'destination' => $checkin->destinationName,
            ],
            locale: $this->user->language
        );

        return Event::create()
            ->name($name)
            ->uniqueIdentifier($checkin->checkinId)
            ->createdAt($checkin->createdAt ? Carbon::parse($checkin->createdAt) : now())
            ->description($this->getDescriptionForCheckin($checkin))
            ->startsAt($checkin->departure ? Carbon::parse($checkin->departure) : now())
            ->endsAt($checkin->arrival ? Carbon::parse($checkin->arrival) : now());
    }

    private function getEventDto(Checkin $checkin): IcsExportStatus
    {
        $tags = [];
        if ($checkin->status->tags->count() > 0) {
            foreach ($checkin->status->tags as $tag) {
                $tags[] = new IcsExportStatusTag(
                    key: $tag->key,
                    value: $tag->value
                );
            }
        }

        return new IcsExportStatus(
            $checkin->originStopover->station->name,
            $checkin->destinationStopover->station->name,
            $checkin->id,
            $checkin->created_at?->toIso8601ZuluString(),
            (string) $checkin->trip->journey_number ?? '',
            $checkin->trip->linename,
            ($this->useRealTime ? $checkin->originStopover->departure : $checkin->originStopover->departure_planned)?->toIso8601ZuluString(),
            ($this->useRealTime ? $checkin->destinationStopover->arrival : $checkin->destinationStopover->arrival_planned)?->toIso8601ZuluString(),
            $tags,
            $checkin->status->body,
            $this->useEmojis ? $checkin?->trip?->category?->getEmoji() : null
        );
    }

    /**
     * @return IcsExportStatus[]
     */
    private function getCheckinsForMonth(Carbon $date, int $limit): array
    {
        $from = $date->startOfMonth()->startOfDay();
        $until = (clone $date)->endOfMonth()->endOfDay();
        Log::debug('Starting checkins for month ICS export', [
            'user_id' => $this->user->id,
            'from' => $from->toDateTimeString(),
            'until' => $until->toDateTimeString(),
        ]);

        $checkinQuery = Checkin::with(['status.tags', 'originStopover.station', 'destinationStopover.station', 'trip.stopovers'])
            ->where('user_id', $this->user->id)
            ->orderByDesc('departure')
            ->limit($limit)
            ->where('departure', '>=', $from)
            ->where('departure', '<=', $until);

        $events = [];
        $checkinQuery->chunk(1000, function ($checkins) use (&$events) {
            foreach ($checkins as $checkin) {
                try {
                    $events[] = $this->getEventDto($checkin, $this->user);
                    Log::debug(
                        'ICS Export: Added checkin to calendar',
                        [
                            'user_id' => $this->user->id,
                            'checkin_id' => $checkin->id,
                            'departure' => $checkin->departure,
                        ]
                    );
                } catch (Throwable $throwable) {
                    report($throwable);
                }
            }
        });

        Log::debug('Finished checkins for month ICS export', [
            'user_id' => $this->user->id,
            'count' => count($events),
        ]);

        return $events;
    }

    private function getDescriptionForCheckin(IcsExportStatus $checkin): string
    {
        $description = '';
        if ($checkin->body !== null) {
            $description .= $checkin->body . PHP_EOL . PHP_EOL;
        }
        $description .= __('export.title.line_name', [], $this->user->language) . ': ' . $checkin->lineName . PHP_EOL;
        if ($checkin->journeyNumber !== null) {
            $description .= __('export.title.journey_number', [], $this->user->language) . ': ' . $checkin->journeyNumber . PHP_EOL;
        }
        if (count($checkin->statusTags) > 0) {
            /** @var IcsExportStatusTag $tag */
            foreach ($checkin->statusTags as $tag) {
                $tagName = __('tag.title.' . $tag->key, [], $this->user->language) !== 'tag.title.' . $tag->key
                    ? __('tag.title.' . $tag->key, [], $this->user->language)
                    : $tag->key;

                $description .= $tagName . ': ' . $tag->value . PHP_EOL;
            }
        }

        return $description;
    }
}
