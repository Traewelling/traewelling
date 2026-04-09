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
    required: ['client_id', 'client_name', 'total', 'by_day', 'by_event', 'by_response_code'],
    properties: [
        new OA\Property(property: 'client_id', type: 'integer', example: 42),
        new OA\Property(property: 'client_name', type: 'string', example: 'My App'),
        new OA\Property(property: 'total', type: 'integer', example: 150),
        new OA\Property(property: 'by_day', type: 'array', items: new OA\Items(ref: WebhookDayStatsDto::class)),
        new OA\Property(property: 'by_event', type: 'array', items: new OA\Items(ref: WebhookEventStatsDto::class)),
        new OA\Property(property: 'by_response_code', type: 'array', items: new OA\Items(ref: WebhookResponseCodeStatsDto::class)),
    ],
    type: 'object'
)]
class WebhookStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'client_id' => $this->resource['client_id'],
            'client_name' => $this->resource['client_name'],
            'total' => $this->resource['total'],
            'by_day' => $this->resource['by_day'],
            'by_event' => $this->resource['by_event'],
            'by_response_code' => $this->resource['by_response_code'],
        ];
    }
}
