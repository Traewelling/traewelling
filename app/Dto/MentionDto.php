<?php

declare(strict_types=1);

namespace App\Dto;

use App\Http\Resources\UserResource;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="MentionDto",
 *     description="Mentioned user and position in status body",
 *     @OA\Xml(
 *         name="MentionDto"
 *     )
 * )
 */
class MentionDto implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     title="user",
     *     description="",
     *     nullable="true",
     *     ref="#/components/schemas/User"
     * )
     */
    public readonly User $user;
    /**
     * @OA\Property(
     *     title="position",
     *     description="",
     *     format="int64",
     *     example=0
     * )
     */
    public readonly int $position;
    /**
     * @OA\Property(
     *     title="length",
     *     description="",
     *     format="integer",
     *     example=4
     * )
     */
    public readonly int $length;

    public function __construct(User $user, int $position, int $length) {
        $this->user     = $user;
        $this->position = $position;
        $this->length   = $length;
    }

    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    public function toArray(): array {
        return [
            'user'     => new UserResource($this->user),
            'position' => $this->position,
            'length'   => $this->length
        ];
    }
}
