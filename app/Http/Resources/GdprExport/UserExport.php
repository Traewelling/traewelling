<?php

namespace App\Http\Resources\GdprExport;

use App\Models\User;

abstract class UserExport
{
    public static function toArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'home_id' => $user->home_id,
            'private_profile' => $user->private_profile,
            'default_status_visibility' => $user->default_status_visibility,
            'prevent_index' => $user->prevent_index,
            'privacy_hide_days' => $user->privacy_hide_days,
            'language' => $user->language,
            'timezone' => $user->timezone,
            'friend_checkin' => $user->friend_checkin,
            'likes_enabled' => $user->likes_enabled,
            'points_enabled' => $user->points_enabled,
            'mapprovider' => $user->mapprovider,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'privacy_ack_at' => $user->privacy_ack_at,
            'last_login' => $user->last_login,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
