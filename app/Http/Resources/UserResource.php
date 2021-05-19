<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id'                => (int) $this->id,
            'displayname'       => (string) $this->name,
            'username'          => (string) $this->username,
            'train_distance'    => (float) $this->train_distance,
            'train_duration'    => (int) $this->train_duration,
            'train_speed'       => (float) $this->averageSpeed,
            'points'            => (int) $this->points,
            'twitter_url'       => $this->twitterUrl ?? null,
            'mastodon_url'      => $this->mastodonUrl ?? null,
            'private_profile'   => (bool) $this->private_profile,
            'userInvisibleToMe' => (bool) $this->userInvisibleToMe
        ];
    }
}
