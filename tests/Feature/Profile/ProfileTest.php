<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class ProfileTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testUnauthenticatedProfileView(): void {
        $userToView = User::factory(['private_profile' => false])->create();

        $this->assertGuest();
        $response = $this->get(route('profile', ['username' => $userToView->username]));
        $response->assertOk();
        $response->assertDontSee(__('profile.private-profile-text'));
    }

    public function testUnauthenticatedPrivateProfileView(): void {
        $userToView = User::factory(['private_profile' => true])->create();

        $this->assertGuest();
        $response = $this->get(route('profile', ['username' => $userToView->username]));
        $response->assertOk();
        $response->assertSee(__('profile.private-profile-text'));
    }
}
