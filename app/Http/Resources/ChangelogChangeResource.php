<?php

namespace App\Http\Resources;

use App\Dto\ChangelogItemDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChangelogChangeResource',
    required: ['emoji', 'info', 'fullText'],
    properties: [
        new OA\Property(
            property: 'emoji',
            description: 'The emoji representing the type of change. See gitmoji.com for reference.',
            example: '🐛',
        ),
        new OA\Property(
            property: 'info',
            description: 'A short description of the change.',
            example: 'Added new feature X',
        ),
        new OA\Property(
            property: 'fullText',
            description: 'The full markdown line from the changelog, including the emoji and any additional',
            example: '* :bug: Fixed this and that by @username'
        ),
    ],
    type: 'object',
)]
class ChangelogChangeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /**
         * @var ChangelogItemDto $this
         */
        return [
            'emoji' => $this->type,
            'info' => $this->shortenedLine,
            'fullText' => $this->markdownLine,
        ];
    }
}
