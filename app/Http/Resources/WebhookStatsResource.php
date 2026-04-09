<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\WebhookDayStatsDto;
use App\Dto\WebhookEventStatsDto;
use App\Dto\WebhookResponseCodeStatsDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookStatsResource',
    description: 'Webhook call log statistics for an OAuth application over the last 7 days',
    required: ['clientId', 'clientName', 'total', 'byDay', 'byEvent', 'byResponseCode'],
    properties: [
        new OA\Property(property: 'clientId', type: 'integer', example: 42),
        new OA\Property(property: 'clientName', type: 'string', example: 'My App'),
        new OA\Property(property: 'total', type: 'integer', example: 150),
        new OA\Property(property: 'byDay', type: 'array', items: new OA\Items(ref: WebhookDayStatsDto::class)),
        new OA\Property(property: 'byEvent', type: 'array', items: new OA\Items(ref: WebhookEventStatsDto::class)),
        new OA\Property(property: 'byResponseCode', type: 'array', items: new OA\Items(ref: WebhookResponseCodeStatsDto::class)),
    ],
    type: 'object'
)]
class WebhookStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'clientId' => $this->resource['client_id'],
            'clientName' => $this->resource['client_name'],
            'total' => $this->resource['total'],
            'byDay' => $this->resource['by_day'],
            'byEvent' => $this->resource['by_event'],
            'byResponseCode' => $this->resource['by_response_code'],
        ];
    }
}
