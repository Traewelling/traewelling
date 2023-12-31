<?php

namespace Feature\Commands;

use App\Http\Controllers\Backend\User\AccountDeletionController;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{

    use RefreshDatabase;

    public function testAccountSelectors(): void {
        // user logged in now
        $user = User::factory(['last_login' => now()])->create();
        $this->assertCount(0, AccountDeletionController::getInactiveUsers());
        $this->assertCount(0, AccountDeletionController::getInactiveUsersWithTwoWeeksLeft());

        // user logged 1 week before a year ago => should be notified
        $user->update(['last_login' => now()->subWeeks(51)]);
        $this->assertCount(0, AccountDeletionController::getInactiveUsers());
        $this->assertCount(1, AccountDeletionController::getInactiveUsersWithTwoWeeksLeft());

        // user logged 1 year ago => should be deleted
        $user->update(['last_login' => now()->subWeeks(53)]);
        $this->assertCount(1, AccountDeletionController::getInactiveUsers());
        $this->assertCount(1, AccountDeletionController::getInactiveUsersWithTwoWeeksLeft());

        // now create a status in the last week so the user is protected from deletion
        Checkin::factory(['user_id' => $user->id])->create();

        // user logged 1 week before a year ago => should NOT be notified anymore (because of the status)
        $user->update(['last_login' => now()->subWeeks(51)]);
        $this->assertCount(0, AccountDeletionController::getInactiveUsers());
        $this->assertCount(0, AccountDeletionController::getInactiveUsersWithTwoWeeksLeft());

        // user logged 1 year ago => should NOT be deleted anymore (because of the status)
        $user->update(['last_login' => now()->subWeeks(53)]);
        $this->assertCount(0, AccountDeletionController::getInactiveUsers());
        $this->assertCount(0, AccountDeletionController::getInactiveUsersWithTwoWeeksLeft());
    }

    public function testAccountDeletionNotificationTwoWeeksBefore(): void {
        Mail::fake();

        Mail::assertSentCount(0);
        $user = User::factory(['last_login' => now()->subWeeks(51)])->create();
        $this->assertCount(1, AccountDeletionController::getInactiveUsersWithTwoWeeksLeft());
        $this->assertFalse(AccountDeletionController::wasNotifiedAboutAccountDeletion($user));

        AccountDeletionController::sendAccountDeletionNotificationTwoWeeksBefore();
        $this->assertTrue(AccountDeletionController::wasNotifiedAboutAccountDeletion($user));

        Mail::assertSentCount(1);

        // test if the mail is sent only once
        AccountDeletionController::sendAccountDeletionNotificationTwoWeeksBefore();
        $this->assertTrue(AccountDeletionController::wasNotifiedAboutAccountDeletion($user));
        Mail::assertSentCount(1);

        // edge case: account is not deleted three weeks after the notification, then it should be resent
        $this->travel(3)->weeks();
        AccountDeletionController::sendAccountDeletionNotificationTwoWeeksBefore();
        $this->assertTrue(AccountDeletionController::wasNotifiedAboutAccountDeletion($user));
        Mail::assertSentCount(2);
    }

    public function testAccountDeletion(): void {
        $user = User::factory(['last_login' => now()->subWeeks(53)])->create();
        $this->assertCount(1, AccountDeletionController::getInactiveUsers());
        $this->assertFalse(AccountDeletionController::wasNotifiedAboutAccountDeletion($user));

        //Account should not be deleted because the user was not notified
        AccountDeletionController::deleteInactiveUsers();
        $this->assertCount(1, AccountDeletionController::getInactiveUsers());
        $this->assertFalse(AccountDeletionController::wasNotifiedAboutAccountDeletion($user));

        //now notify the user
        AccountDeletionController::sendAccountDeletionNotificationTwoWeeksBefore();
        $this->assertTrue(AccountDeletionController::wasNotifiedAboutAccountDeletion($user));

        //travel two weeks into the future and delete the user - he should be deleted now
        $this->travel(2)->weeks();
        AccountDeletionController::deleteInactiveUsers();
        $this->assertCount(0, AccountDeletionController::getInactiveUsers());
    }
}
