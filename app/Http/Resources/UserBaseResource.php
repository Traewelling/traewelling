<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBaseResource extends JsonResource
{
    public function toArray($request): array {
        $pointsEnabled = $request->user()?->points_enabled ?? true;
        return [
            'id'             => (int) $this->id,
            'displayName'    => (string) $this->name,
            'username'       => (string) $this->username,
            'profilePicture' => ProfilePictureController::getUrlForUserId($this->id),
            'trainDistance'  => (float) $this->train_distance, // @deprecated: remove after 2024-08
            'totalDistance'  => (float) $this->train_distance,
            'trainDuration'  => (int) $this->train_duration, // @deprecated: remove after 2024-08
            'totalDuration'  => (int) $this->train_duration,
            'points'         => (int) $pointsEnabled ? $this->points : 0,
            'mastodonUrl'    => $this->mastodonUrl ?? null,
            'privateProfile' => (bool) $this->private_profile,
            'preventIndex'   => $this->prevent_index,
            'likes_enabled'  => $this->likes_enabled,
            'pointsEnabled'  => $this->points_enabled,
            $this->mergeWhen(isset($this->UserSettingsResource),
                [
                    'home'                    => $this->home,
                    'language'                => $this->language,
                    'defaultStatusVisibility' => $this->default_status_visibility,
                    'friendCheckin'           => $this->friend_checkin,
                ]),
            $this->mergeWhen(isset($this->UserResource),
                [
                    'userInvisibleToMe' => (bool) $request->user()?->cannot('view', User::find($this->id)),
                    'muted'             => (bool) $this->muted,
                    'following'         => (bool) $this->following,
                    'followPending'     => (bool) $this->followPending,
                ])
        ];
    }
}
