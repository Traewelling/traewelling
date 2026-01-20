<?php

namespace App\Http\Resources;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AlertResource",
 *     required={"id", "type", "active_from", "active_until", "url", "translations"},
 *
 *     @OA\Property(property="id", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="type", type="enum", enum={"info", "warning", "danger", "success"}, example="info"),
 *     @OA\Property(property="active_from", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
 *     @OA\Property(property="active_until", type="string", format="date-time", example="2023-10-31T23:59:59Z", nullable=true),
 *     @OA\Property(property="url", type="string", example="https://example.com", nullable=true),
 *     @OA\Property(
 *     property="translations",
 *
 *     @OA\Items(ref="#/components/schemas/AlertTranslationResource"),
 *     type="array",
 *     )
 * )
 */
class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Alert $this */
        return [
            'id' => $this->id,
            'type' => $this->type,
            'active_from' => $this->active_from,
            'active_until' => $this->active_until,
            'url' => $this->url,
            'translations' => AlertTranslationResource::collection($this->translations),
        ];
    }
}
