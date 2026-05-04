<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Internal\IcsExportStatus;
use App\Helpers\CacheKey;
use App\Models\IcsToken;
use App\Models\User;
use App\Repositories\CheckinRepository;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

readonly class IcsExportService
{
    public function __construct(
        private User $user,
        private bool $useEmojis = true,
        private CheckinRepository $repository = new CheckinRepository(),
    ) {}

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

        [$from, $until] = $this->clampToCheckinBounds($from, $until);

        Log::debug('Starting ICS export', [
            'user_id' => $this->user->id,
            'from' => $from->toDateTimeString(),
            'until' => $until->toDateTimeString(),
        ]);

        if ($from->isAfter($until)) {
            return $calendar;
        }

        $date = $from->copy()->startOfMonth();
        while ($date->lessThanOrEqualTo($until)) {
            foreach ($this->getCachedCheckinsForMonth($date, $limit) as $event) {
                $calendar->event($this->buildEvent($event));
            }
            $date->addMonth();
        }

        $icsToken->update(['last_accessed' => now()]);

        return $calendar;
    }

    /** @return array{Carbon, Carbon} */
    private function clampToCheckinBounds(Carbon $from, Carbon $until): array
    {
        $bounds = $this->repository->getDepartureBoundsForUser($this->user);

        if ($bounds['first'] !== null) {
            $from = max([$from, $bounds['first']->copy()->startOfMonth()]);
        }

        if ($bounds['last'] !== null) {
            $until = min([$until, $bounds['last']->copy()->endOfMonth()]);
        }

        return [$from, $until];
    }

    /** @return IcsExportStatus[] */
    private function getCachedCheckinsForMonth(Carbon $date, int $limit): array
    {
        return Cache::remember(
            key: CacheKey::getIcsUserMonthlyKey($this->user, $date),
            ttl: $this->cacheTtlForMonth($date),
            callback: fn () => $this->repository->getCheckinsAsIcsStatusForMonth(
                $this->user,
                $date->copy(),
                $limit,
                $this->useEmojis,
            ),
        );
    }

    private function cacheTtlForMonth(Carbon $date): int
    {
        if ($date->isBefore(now()->startOfMonth())) {
            return (int) now()->addDays(rand(3, 6))->diffInSeconds();
        }

        return (int) now()->addMinutes(15)->diffInSeconds();
    }

    private function buildEvent(IcsExportStatus $checkin): Event
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
            locale: $this->user->language,
        );

        return Event::create()
            ->name($name)
            ->uniqueIdentifier($checkin->checkinId)
            ->createdAt($checkin->createdAt ? Carbon::parse($checkin->createdAt) : now())
            ->description($this->buildDescription($checkin))
            ->startsAt($checkin->departure ? Carbon::parse($checkin->departure) : now())
            ->endsAt($checkin->arrival ? Carbon::parse($checkin->arrival) : now());
    }

    private function buildDescription(IcsExportStatus $checkin): string
    {
        $description = '';
        if ($checkin->body !== null) {
            $description .= $checkin->body . PHP_EOL . PHP_EOL;
        }
        $description .= __('export.title.line_name', [], $this->user->language) . ': ' . $checkin->lineName . PHP_EOL;
        if ($checkin->journeyNumber !== '') {
            $description .= __('export.title.journey_number', [], $this->user->language) . ': ' . $checkin->journeyNumber . PHP_EOL;
        }
        foreach ($checkin->statusTags as $tag) {
            $tagName = __('tag.title.' . $tag->key, [], $this->user->language) !== 'tag.title.' . $tag->key
                ? __('tag.title.' . $tag->key, [], $this->user->language)
                : $tag->key;
            $description .= $tagName . ': ' . $tag->value . PHP_EOL;
        }

        return $description;
    }
}
