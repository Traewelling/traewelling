<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\WebhookDayStatsDto;
use App\Dto\WebhookEventStatsDto;
use App\Dto\WebhookResponseCodeStatsDto;
use App\Models\WebhookCallLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WebhookCallLogRepository
{
    /**
     * @return array{total: int, by_day: Collection<WebhookDayStatsDto>, by_event: Collection<WebhookEventStatsDto>, by_response_code: Collection<WebhookResponseCodeStatsDto>}
     */
    public function getStats(int $clientId, Carbon $since): array
    {
        $logs = WebhookCallLog::where('oauth_client_id', $clientId)
            ->where('created_at', '>=', $since)
            ->get(['created_at', 'event', 'response_code']);

        $byDay = $logs
            ->groupBy(fn ($log) => $log->created_at->toDateString())
            ->map(fn ($group, $date) => new WebhookDayStatsDto(
                date: $date,
                total: $group->count(),
                success: $group->filter(fn ($l) => $l->response_code >= 200 && $l->response_code < 300)->count(),
                failed: $group->filter(fn ($l) => $l->response_code === null || $l->response_code < 200 || $l->response_code >= 300)->count(),
            ))
            ->sortKeys()
            ->values();

        $byEvent = $logs
            ->groupBy('event')
            ->map(fn ($group, $event) => new WebhookEventStatsDto(
                event: $event,
                total: $group->count(),
            ))
            ->sortByDesc('total')
            ->values();

        $byResponseCode = $logs
            ->groupBy(fn ($l) => $l->response_code ?? 'timeout')
            ->map(fn ($group, $key) => new WebhookResponseCodeStatsDto(
                response_code: is_numeric($key) ? (int) $key : null,
                total: $group->count(),
            ))
            ->sortByDesc('total')
            ->values();

        return [
            'total' => $logs->count(),
            'by_day' => $byDay,
            'by_event' => $byEvent,
            'by_response_code' => $byResponseCode,
        ];
    }
}
