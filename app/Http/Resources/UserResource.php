<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array {
        return [
            'id'                => (int) $this->id,
            'displayName'       => (string) $this->name,
            'username'          => (string) $this->username,
            'trainDistance'     => (float) $this->train_distance,
            'trainDuration'     => (int) $this->train_duration,
            'trainSpeed'        => (float) $this->averageSpeed,
            'points'            => (int) $this->points,
            'twitterUrl'        => $this->twitterUrl ?? null,
            'mastodonUrl'       => $this->mastodonUrl ?? null,
            'privateProfile'    => (bool) $this->private_profile,
            'userInvisibleToMe' => (bool) \request()->user()->cannot('view', $this), //TODO: Is $this working here?
            'muted'             => (bool) $this->muted,
            'following'         => (bool) $this->following,
            'followPending'     => (bool) $this->followPending,
            'preventIndex'      => (bool) $this->prevent_index,
        ];
    }
}
