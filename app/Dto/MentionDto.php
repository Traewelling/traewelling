<?php

declare(strict_types=1);

namespace App\Dto;

use App\Http\Resources\UserResource;
use App\Models\User;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Mention',
    required: ['user', 'position', 'length'],
    description: 'Mentioned user and position in status body',
    xml: new OA\Xml(name: 'Mention'),
)]
readonly class MentionDto implements \JsonSerializable
{
    #[OA\Property(title: 'user', nullable: true, ref: '#/components/schemas/UserResource')]
    public User $user;

    #[OA\Property(title: 'position', format: 'int', example: 0)]
    public int $position;

    #[OA\Property(title: 'length', format: 'integer', example: 4)]
    public int $length;

    public function __construct(User $user, int $position, int $length)
    {
        $this->user = $user;
        $this->position = $position;
        $this->length = $length;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'user' => new UserResource($this->user),
            'position' => $this->position,
            'length' => $this->length,
        ];
    }
}
