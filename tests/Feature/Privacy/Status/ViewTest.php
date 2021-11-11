<?php

namespace Tests\Feature\Privacy\Status;

use App\Enum\StatusVisibility;
use App\Models\Follow;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewTest extends TestCase
{

    use RefreshDatabase;

    public function testViewOnStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testViewForeignPublicStatus(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status      = Status::factory([
                                           'user_id'    => $foreignUser->id,
                                           'visibility' => StatusVisibility::PUBLIC,
                                       ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testViewForeignFollowersOnlyStatusAndNotFollowing(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status      = Status::factory([
                                           'user_id'    => $foreignUser->id,
                                           'visibility' => StatusVisibility::FOLLOWERS,
                                       ])->create();
        $this->assertFalse($user->can('view', $status));
    }

    public function testViewForeignFollowersOnlyStatusAndIsFollowing(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        Follow::factory([
                            'user_id'   => $user->id,
                            'follow_id' => $foreignUser->id,
                        ])->create();
        $status = Status::factory([
                                      'user_id'    => $foreignUser->id,
                                      'visibility' => StatusVisibility::FOLLOWERS,
                                  ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testViewForeignPrivateStatus(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status      = Status::factory([
                                           'user_id'    => $foreignUser->id,
                                           'visibility' => StatusVisibility::PRIVATE,
                                       ])->create();
        $this->assertFalse($user->can('view', $status));
    }
}
