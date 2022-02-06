<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|UserBaseResource
     * @todo remove twitterUrl after replaced url in vue profile template (it's unused)
     */
    public function toArray($request): array {
        return [
            'id'             => (int) $this->id,
            'displayName'    => (string) $this->name,
            'username'       => (string) $this->username,
            'profilePicture' => ProfilePictureController::getUrlForUserId($this->id),
            'trainDistance'  => (float) $this->train_distance,
            'trainDuration'  => (int) $this->train_duration,
            'trainSpeed'     => (float) $this->averageSpeed,
            'points'         => (int) $this->points,
            'twitterUrl'     => $this->twitterUrl ?? null,
            'mastodonUrl'    => $this->mastodonUrl ?? null,
            'privateProfile' => (bool) $this->private_profile,
            $this->mergeWhen(isset($this->UserSettingsResource),
                [
                    'role'          => $this->role,
                    'home'          => $this->home,
                    'private'       => $this->private_profile,
                    'prevent_index' => $this->prevent_index,
                    'dbl'           => $this->always_dbl,
                    'language'      => $this->language
                ]),
            $this->mergeWhen(isset($this->UserResource),
                [
                    'userInvisibleToMe' => (bool) $this->userInvisibleToMe,
                    'muted'             => (bool) $this->muted,
                    'following'         => (bool) $this->following,
                    'followPending'     => (bool) $this->followPending,
                    'preventIndex'      => (bool) $this->prevent_index,
                ]),
        ];
    }
}
