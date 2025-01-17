<?php

namespace App\Http\Controllers\Backend\User;

use App\Helpers\CacheKey;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\UserController;
use App\Mail\AccountDeletionNotificationTwoWeeksBefore;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

abstract class AccountDeletionController extends Controller
{

    private static function getInactiveUsersSinceWeeks(int $weeks): Collection {
        return User::where('users.last_login', '<', now()->subWeeks($weeks))
                   ->whereNotNull('email')
                   ->get()
                   ->filter(static function(User $user) use ($weeks) {
                       return $user->statuses()->where('statuses.created_at', '>', now()->subWeeks($weeks))->count() === 0;
                   });
    }

    public static function getInactiveUsers(): Collection {
        return self::getInactiveUsersSinceWeeks(52);
    }

    public static function getInactiveUsersWithTwoWeeksLeft(): Collection {
        return self::getInactiveUsersSinceWeeks(50);
    }

    public static function sendAccountDeletionNotificationTwoWeeksBefore(): void {
        if (!config('app.privacy.account-deletion.send-notification')) {
            Log::info('Skipping sending of account deletion notifications because it is disabled in the config');
            return;
        }

        $inactiveUsersWithTwoWeeksLeft = self::getInactiveUsersWithTwoWeeksLeft();
        foreach ($inactiveUsersWithTwoWeeksLeft as $user) {
            RateLimiter::attempt(
                key:          CacheKey::getAccountDeletionNotificationTwoWeeksBeforeKey($user),
                maxAttempts:  1,
                callback: static function() use ($user) {
                    Log::info('Sending account deletion notification to user ' . $user->id . ' (' . $user->email . ')');
                    Mail::to($user)->send(new AccountDeletionNotificationTwoWeeksBefore($user));
                },
                decaySeconds: 60 * 60 * 24 * 7 * 3 // 3 weeks (to prevent sending the mail again)
            );
        }
    }

    public static function deleteInactiveUsers(): void {
        if (!config('app.privacy.account-deletion.delete-account')) {
            Log::info('Skipping deletion of inactive users because it is disabled in the config');
            return;
        }

        $inactiveUsers = self::getInactiveUsers();
        foreach ($inactiveUsers as $user) {
            try {
                if (!self::wasNotifiedAboutAccountDeletion($user)) {
                    Log::info('Skipping inactive user ' . $user->id . ' (' . $user->email . ') because he was not notified about the account deletion');
                    continue;
                }

                Log::info('Deleting inactive user ' . $user->id . ' (' . $user->email . ')');
                UserController::deleteUserAccount($user);
            } catch (Exception $e) {
                Log::error('Error deleting inactive user ' . $user->id . ' (' . $user->email . '): ' . $e->getMessage());
            }
        }
    }

    public static function wasNotifiedAboutAccountDeletion(User $user): bool {
        return RateLimiter::tooManyAttempts(
            key:         CacheKey::getAccountDeletionNotificationTwoWeeksBeforeKey($user),
            maxAttempts: 1,
        );
    }
}
