<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

/**
 * Load the publicly accessible files that any visitor can load without logging in.
 * For all of these tests:
 *
 * GIVEN: Our codebase
 * WHEN: A non-logged-in user tries to reach those pages
 * THEN: Show them to the user.
 */
class StaticPagesThatMightHaveComputedPropertiesTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testHomepageGet() {
        $response = $this->get('/');
        $response->assertOk();
    }

    public function testLoginGet() {
        $response = $this->get('/login');
        $response->assertOk();
    }

    public function testRegisterGet() {
        $response = $this->get('/register');
        $response->assertOk();
    }

    public function testLeaderboardGet() {
        $response = $this->get('/leaderboard');
        $response->assertOk();
    }

    public function testLegalNoticeGet() {
        $response = $this->get('/legal/');
        $response->assertOk();
    }

    public function testPrivacyGet() {
        $response = $this->get('/legal/privacy-policy');
        $response->assertOk();
    }

    public function testProfilePageGet() {
        // GIVEN: A gdpr-acked user
        $user = User::factory()->create();

        // WHEN: Someone visits the user's profile page
        $response = $this->get(route('profile', ["username" => $user->username]));

        // THEN: The page is rendered and shows the user's name and username
        $response->assertOk();
        $response->assertSee($user->name, false);
        $response->assertSee($user->username, false);
    }
}
