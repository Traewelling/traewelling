<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Route;
use Tests\TestCase;

class UserRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void {
        parent::setUp();

        Route::middleware('userrole:0')->any('/_test/role_user', function () { return 'OK'; });
        Route::middleware('userrole:5')->any('/_test/role_mod', function () { return 'OK'; });
        Route::middleware('userrole:10')->any('/_test/role_admin', function () { return 'OK'; });
    }

    /**
     * Guests can do some things, others would usually get catched by the Auth middleware.
     * @test
     */
    public function guests_can_get_some_pages() {
        // Given: There's a guest, without a user object.

        // When: Requesting any guest pages
        // Then: We're good!
        $userPage = $this->get('/_test/role_user');
        $userPage->assertOk();

        // When: Requesting any mod-restricted page
        // Then: We're redirected to /login
        $userPage = $this->get('/_test/role_mod');
        $userPage->assertStatus(401);

        // When: Requesting any admin-restricted page
        // Then: We're redirected to /login
        $userPage = $this->get('/_test/role_admin');
        $userPage->assertStatus(401);
    }

    /**
     * Normal, logged in users should be able to see guest and user pages, but not mod or admin pages.
     * @test
     */
    public function normal_users_can_get_some_pages() {
        // Given: There's a user with role=0 (Normal user)
        $user       = User::factory()->create();
        $user->role = 0;

        // When: Requesting any guest pages
        // Then: We're good!
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_user');
        $userPage->assertOk();

        // When: Requesting any mod-restricted page
        // Then: We're redirected to /login
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_mod');
        $userPage->assertStatus(401);

        // When: Requesting any admin-restricted page
        // Then: We're redirected to /login
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_admin');
        $userPage->assertStatus(401);
    }

    /**
     * Moderators should be able to see most pages, but not admin pages.
     * @test
     */
    public function moderators_can_get_some_pages() {
        // Given: There's a user with role=0 (Normal user)
        $user       = User::factory()->create();
        $user->role = 5;

        // When: Requesting any guest pages
        // Then: We're good!
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_user');
        $userPage->assertOk();

        // When: Requesting any mod-restricted page
        // Then: We're redirected to /login
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_mod');
        $userPage->assertOk();

        // When: Requesting any admin-restricted page
        // Then: We're redirected to /login
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_admin');
        $userPage->assertStatus(401);
    }

    /**
     * Admins can get all pages.
     * @test
     */
    public function admins_can_get_all_pages() {
        // Given: There's a user with role=0 (Normal user)
        $user       = User::factory()->create();
        $user->role = 10;

        // When: Requesting any guest pages
        // Then: We're good!
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_user');
        $userPage->assertOk();

        // When: Requesting any mod-restricted page
        // Then: We're redirected to /login
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_mod');
        $userPage->assertOk();

        // When: Requesting any admin-restricted page
        // Then: We're redirected to /login
        $userPage = $this->actingAs($user)
                     ->get('/_test/role_admin');
        $userPage->assertOk();
    }
}
