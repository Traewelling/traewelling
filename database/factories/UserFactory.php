<?php

namespace Database\Factories;

use App\Enum\StatusVisibility;
use App\Enum\User\FriendCheckinSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array {
        return [
            'name'                      => $this->faker->name,
            'username'                  => $this->faker->unique()->userName,
            'avatar'                    => $this->getAvatar(),
            'email'                     => $this->faker->unique()->safeEmail,
            'email_verified_at'         => now(),
            'privacy_ack_at'            => now(),
            'password'                  => Hash::make('password'),
            'home_id'                   => null,
            'private_profile'           => false,
            'default_status_visibility' => StatusVisibility::PUBLIC->value,
            'prevent_index'             => false,
            'privacy_hide_days'         => 7,
            'language'                  => null,
            'likes_enabled'             => true,
            'points_enabled'            => true,
            'friend_checkin'            => FriendCheckinSetting::FORBIDDEN,
        ];
    }

    private function getAvatar(): ?string {
        if ($this->faker->boolean(20)) {
            //sometimes we wanna users without avatar - so we can test this case too.
            return null;
        }
        $image = $this->getAvatarImage();

        File::copy($image, public_path('uploads/avatars/' . basename($image)));
        return basename($image);
    }

    private function getAvatarImage(): string {
        $files = glob(base_path('tests/static/avatars/*'));
        return $files[array_rand($files)];
    }
}
