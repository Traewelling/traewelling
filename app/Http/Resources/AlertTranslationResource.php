<?php

namespace App\Http\Resources;

use App\Models\AlertTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AlertTranslationResource',
    required: ['title', 'content', 'url', 'locale'],
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Alert Title'),
        new OA\Property(property: 'content', type: 'string', example: 'Alert Content'),
        new OA\Property(property: 'url', type: 'string', example: 'https://example.com'),
        new OA\Property(property: 'locale', type: 'string', example: 'en'),
    ],
)]
class AlertTranslationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var AlertTranslation $this */
        return [
            'title' => $this->title,
            'content' => $this->content,
            'url' => $this->url,
            'locale' => $this->locale,
        ];
    }
}
