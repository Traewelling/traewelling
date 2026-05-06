<?php

namespace App\Http\Controllers\Backend;

use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailChangedMail;
use App\Jobs\SendVerificationEmail;
use App\Models\MailChange;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

abstract class SettingsController extends Controller
{
    /**
     * @throws RateLimitExceededException if the user has exceeded the rate limit for sending verification emails
     */
    public static function updateSettings(array $fields, ?User $user = null): Authenticatable|null|User
    {
        if ($user === null) {
            $user = auth()->user();
        }

        if (array_key_exists('experimental', $fields)) {
            if ($fields['experimental'] && !$user->hasRole('open-beta')) {
                auth()->user()->assignRole('open-beta');
            } elseif (!$fields['experimental'] && $user->hasRole('open-beta')) {
                auth()->user()->removeRole('open-beta');
            }
        }

        if (array_key_exists('profileLinks', $fields)) {
            self::updateProfileLinks($fields['profileLinks'], $user);
            unset($fields['profileLinks']);
        }

        // map api fields to model fields for update
        // this is necessary because the API uses different field names than the model
        // don't add your field if your api field is identical to the model field
        $mappings = [
            'displayName' => 'name',
            'friendCheckin' => 'friend_checkin',
            'privateProfile' => 'private_profile',
            'likesEnabled' => 'likes_enabled',
            'pointsEnabled' => 'points_enabled',
            'preventIndex' => 'prevent_index',
            'privacyHideDays' => 'privacy_hide_days',
            'defaultStatusVisibility' => 'default_status_visibility',
            'mapProvider' => 'mapprovider',
            'dataProvider' => 'data_provider',
        ];
        foreach ($mappings as $apiField => $modelField) {
            if (array_key_exists($apiField, $fields)) {
                $fields[$modelField] = $fields[$apiField];
                unset($fields[$apiField]);
            }
        }

        $user->update($fields);

        if (array_key_exists('mastodonVisibility', $fields)) {
            $user->socialProfile->update(['mastodon_visibility' => $fields['mastodonVisibility']]);
        }

        return $user;
    }

    public static function updateMail(string $newMail, User $user): User
    {
        $change = MailChange::create([
            'user_id' => $user->id,
            'old_email' => $user->email,
            'new_email' => $newMail,
        ]);

        if ($change->old_email !== null) {
            SendEmailChangedMail::dispatch($user, $change);
        }

        $user->email = $newMail;
        $user->email_verified_at = null;
        $user->save();
        self::sendEmailVerificationNotification($user);

        return $user;
    }

    private static function updateProfileLinks(array $profileLinks, User $user): void
    {
        $user->profileLinks()->delete();
        foreach ($profileLinks as $link) {

            $user->profileLinks()->create([
                'name' => $link['name'],
                'url' => $link['url'],
            ]);
        }
    }

    public static function sendEmailVerificationNotification(User $user): void
    {
        Log::info('Attempting to send verification email.', ['user_id' => $user->id, 'email' => $user->email]);

        $executed = RateLimiter::attempt(
            key: 'verification-mail-sent-' . $user->email,
            maxAttempts: 1,
            callback: function () use ($user) {
                SendVerificationEmail::dispatch($user);
                Log::info('Dispatched SendVerificationEmail job.', ['user_id' => $user->id, 'email' => $user->email]);
            },
            decaySeconds: 5 * 60,
        );

        if (!$executed) {
            Log::info(sprintf(
                'Sending the verification email for user#%s w/mail %s was rate-limited.',
                $user->id,
                $user->email
            ));
            throw new RateLimitExceededException();
        }
    }
}
