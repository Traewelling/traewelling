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
        $base = WebhookCallLog::where('oauth_client_id', $clientId)
            ->where('created_at', '>=', $since);

        $total = (clone $base)->count();

        $byDay = (clone $base)
            ->selectRaw(
                'DATE(created_at) as date, COUNT(*) as total,'
                . ' SUM(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 ELSE 0 END) as success,'
                . ' SUM(CASE WHEN response_code IS NULL OR response_code < 200 OR response_code >= 300 THEN 1 ELSE 0 END) as failed'
            )
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => new WebhookDayStatsDto(
                date: $row->date,
                total: (int) $row->total,
                success: (int) $row->success,
                failed: (int) $row->failed,
            ))
            ->values();

        $byEvent = (clone $base)
            ->selectRaw('event, COUNT(*) as total')
            ->groupBy('event')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => new WebhookEventStatsDto(
                event: $row->event,
                total: (int) $row->total,
            ))
            ->values();

        $byResponseCode = (clone $base)
            ->selectRaw('response_code, COUNT(*) as total')
            ->groupBy('response_code')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => new WebhookResponseCodeStatsDto(
                responseCode: $row->response_code !== null ? (int) $row->response_code : null,
                total: (int) $row->total,
            ))
            ->values();

        return [
            'total' => $total,
            'by_day' => $byDay,
            'by_event' => $byEvent,
            'by_response_code' => $byResponseCode,
        ];
    }
}
