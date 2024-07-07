<?php

namespace App\Http\Resources;

use App\Dto\MentionDto;
use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Models\Status;
use App\Models\StatusTag;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Schema(
 *      title="Status",
 *      @OA\Property(property="id", type="integer", example=12345),
 *      @OA\Property(property="body", description="User defined status text", example="Hello world!"),
 *      @OA\Property(property="bodyMentions", description="Mentions in the status body", type="array", @OA\Items(ref="#/components/schemas/MentionDto")),
 *      @OA\Property(property="business", ref="#/components/schemas/Business"),
 *      @OA\Property(property="visibility", ref="#/components/schemas/StatusVisibility"),
 *      @OA\Property(property="likes", description="How many people have liked this status", type="integer", example=12),
 *      @OA\Property(property="liked", description="Did the currently authenticated user like this status? (if unauthenticated = false)",type="boolean",example=true),
 *      @OA\Property(property="isLikable", description="Do the author of this status and the currently authenticated user allow liking of statuses? Only show the like UI if set to true",type="boolean", example=true),
 *      @OA\Property(property="client", ref="#/components/schemas/ClientResource"),
 *      @OA\Property(property="createdAt", description="creation date of this status",type="string",format="datetime", example="2022-07-17T13:37:00+02:00"),
 *      @OA\Property(property="train", ref="#/components/schemas/TransportResource"),
 *      @OA\Property(property="event", ref="#/components/schemas/EventResource", nullable=true),
 *      @OA\Property(property="userDetails", ref="#/components/schemas/LightUserResource"),
 *      @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/StatusTagResource")),
 * )
 */
class StatusResource extends JsonResource
{
    public function toArray($request): array {
        /** @var Status $this */
        return [
            'id'             => (int) $this->id,
            'body'           => (string) $this->body,
            'bodyMentions'   => $this->mentions->map(
                fn($mention) => new MentionDto($mention->mentioned, $mention->position, $mention->length)
            ),
            'user'           => (int) $this->user->id, // TODO: deprectated: remove after 2024-08
            'username'       => (string) $this->user->username, // TODO: deprectated: remove after 2024-08
            'profilePicture' => ProfilePictureController::getUrl($this->user), // TODO: deprectated: remove after 2024-08
            'preventIndex'   => (bool) $this->user->prevent_index, // TODO: deprectated: remove after 2024-08
            'business'       => (int) $this->business->value,
            'visibility'     => (int) $this->visibility->value,
            'likes'          => (int) $this->likes->count(),
            'liked'          => (bool) $this->favorited,
            'isLikable'      => Gate::allows('like', $this->resource),
            'client'         => new ClientResource($this->client),
            'createdAt'      => $this->created_at->toIso8601String(),
            'train'          => new TransportResource($this->checkin), //TODO: don't call it train - we have more than trains
            'event'          => new EventResource($this?->event),
            'userDetails'    => new LightUserResource($this->user), //TODO: rename this to user, after deprecated fields are removed (2024-08)
            'tags'           => StatusTagResource::collection($this->tags->filter(fn(StatusTag $tag) => Gate::allows('view', $tag))),
        ];
    }
}
