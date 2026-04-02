<?php

namespace App\Http\Resources;

use App\Dto\ChangelogEntryDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChangelogResource',
    required: ['title', 'description', 'tagName', 'createdAt', 'changes'],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'The title of the release',
            type: 'string',
            example: '2026-04-01',
        ),
        new OA\Property(
            property: 'description',
            description: 'The markdown description of the release',
            type: 'string',
        ),
        new OA\Property(
            property: 'tagName',
            description: 'The tag name of the release',
            type: 'string',
        ),
        new OA\Property(
            property: 'createdAt',
            description: 'The release date of the release',
            type: 'string',
            format: 'date-time',
        ),
        new OA\Property(
            property: 'changes',
            description: 'The changes of the release',
            type: 'array',
            items: new OA\Items(ref: ChangelogChangeResource::class)
        ),
    ],
    type: 'object'
)]
class ChangelogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var ChangelogEntryDto $this
         */
        return [
            'title' => $this->title,
            'description' => $this->description,
            'tagName' => $this->tag,
            'createdAt' => $this->created->toIso8601ZuluString(),
            'changes' => ChangelogChangeResource::collection($this->entries),
        ];
    }
}
