<?php

use App\Models\SocialLoginProfile;
use Illuminate\Database\Migrations\Migration;

class MigrateEncryptables extends Migration
{
    /**
     * To perform this migration follow these instructions:
     * 1. activate maintenance mode first!
     * 2. backup SocialLoginProfiles!
     * 3. then push the release
     * 4. artisan optimize!
     * 5. artisan migrate
     * 6. deactivate maintenance mode
     * 7. test Twitter/Mastodon posting
     */
    public function up(): void {
        $slProfiles = SocialLoginProfile::whereNotNull('twitter_token')
                                        ->orWhereNotNull('twitter_tokenSecret')
                                        ->orWhereNotNull('mastodon_token')
                                        ->get();

        foreach ($slProfiles as $slProfile) {
            $payload = [];

            if (isset($slProfile->getAttributes()['twitter_token'])) {
                $payload['twitter_token'] = decrypt($slProfile->getAttributes()['twitter_token']);
            }
            if (isset($slProfile->getAttributes()['twitter_tokenSecret'])) {
                $payload['twitter_tokenSecret'] = decrypt($slProfile->getAttributes()['twitter_tokenSecret']);
            }
            if (isset($slProfile->getAttributes()['mastodon_token'])) {
                $payload['mastodon_token'] = decrypt($slProfile->getAttributes()['mastodon_token']);
            }

            $slProfile->update($payload);
        }
    }
}
