<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\User\DashboardController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\ApiTestCase;

class ApiUserTest extends ApiTestCase
{
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        $this->loginGertrudAndAckGDPR();
    }

    /**
     * Retrieve a user profile
     * @test
     */
    public function get_user_profile() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('GET', route('api.v0.user', ['username' => 'gertrud123']));
        $response->assertOk();

        $response->assertJsonStructure([
                                           'username',
                                           'twitterUrl',
                                           'mastodonUrl',
                                           'statuses' => [
                                               'current_page',
                                               'data',
                                               'first_page_url',
                                               'from',
                                               'last_page',
                                               'last_page_url',
                                               'next_page_url',
                                               'path',
                                               'per_page',
                                               'prev_page_url',
                                               'to'
                                           ],
                                           'user'     => [
                                               'id',
                                               'name',
                                               'username',
                                               'train_distance',
                                               'train_duration',
                                               'points'
                                           ]]);
    }

    /**
     * Get current User
     * @test
     */
    public function get_user() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->get(route('api.v0.getUser'));
        $response->assertOk();
        $response->assertJsonStructure(['id',
                                        'name',
                                        'username',
                                        'train_distance',
                                        'train_duration',
                                        'points']);
        $this->assertTrue(json_decode($response->getContent())->username == 'Gertrud123');


    }

    /**
     * Tests the current active status
     * @test
     */
    public function get_active_checkin_for_user() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->get(route('api.v0.user.active', ['username' => 'Gertrud123']));
        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent(), true)));
        //Somehow this throws an error even though the structure is the same.
        $response->assertJsonStructure([
                                           'id',
                                           'created_at',
                                           'updated_at',
                                           'body',
                                           'type',
                                           'event_id',
                                           'user'          => [
                                               'id',
                                               'name',
                                               'username',
                                               'train_distance',
                                               'train_duration',
                                               'points'
                                           ],
                                           'train_checkin' => [
                                               'id',
                                               'status_id',
                                               'trip_id',
                                               'origin'      => [
                                                   'id',
                                                   'ibnr',
                                                   'name',
                                                   'latitude',
                                                   'longitude'
                                               ],
                                               'destination' => [
                                                   'id',
                                                   'ibnr',
                                                   'name',
                                                   'latitude',
                                                   'longitude'
                                               ],
                                               'distance',
                                               'departure',
                                               'arrival',
                                               'points',
                                               'hafas_trip'  => [
                                                   'id',
                                                   'trip_id',
                                                   'category',
                                                   'number',
                                                   'linename',
                                                   'origin',
                                                   'destination',
                                                   'stopovers',
                                                   'departure',
                                                   'arrival',
                                                   'delay'
                                               ]
                                           ],
                                           'event'
                                       ]);
    }

    /**
     * Test the user search
     * @test
     */
    public function get_user_search(): void {
        //Test that user has been found
        $response = $this->withHeaders([
                                           'Authorization' => 'Bearer ' . $this->token,
                                           'Accept'        => 'application/json',
                                       ])
                         ->get(route('api.v0.user.search', 'gertru'));
        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent(), true)));
        $response->assertJsonStructure(['current_page',
                                        'data' => [[
                                                       "id",
                                                       "name",
                                                       "username",
                                                       "train_distance",
                                                       "train_duration",
                                                       "points",
                                                       "averageSpeed"
                                                   ]],
                                        'first_page_url',
                                        'from',
                                        'next_page_url',
                                        'path',
                                        'per_page',
                                        'prev_page_url',
                                        'to']);

        //Test that an unknown user returns 200 and an empty json object
        $response = $this->withHeaders([
                                           'Authorization' => 'Bearer ' . $this->token,
                                           'Accept'        => 'application/json',
                                       ])
                         ->get(route('api.v0.user.search',
                                     'sdfklghbqeörgjaösrjgäIERGKJAEFÖRGJSDÖFJHBÜÄAJRÄÜG'));
        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent(), true)));
        $response->assertJsonStructure(['current_page',
                                        'data' => [],
                                        'first_page_url',
                                        'from',
                                        'next_page_url',
                                        'path',
                                        'per_page',
                                        'prev_page_url',
                                        'to']);
    }

    public function test_private_profile() {

        User::factory()->create([
                                    'username'        => 'Alice123',
                                    'private_profile' => 1
                                ]);

        //Check if non private profiles statuses are not null
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('GET', route('api.v0.user', ['username' => 'gertrud123']));
        $response->assertOk();
        $this->assertTrue(json_decode($response->getContent())->statuses !== null);


        //set Gertrud as private profile and check again if status are not null,
        // because the user should see his own statuses
        $gertrud                  = User::where('username', 'Gertrud123')->first();
        $gertrud->private_profile = true;
        $gertrud->save();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('GET', route('api.v0.user', ['username' => 'gertrud123']));
        $response->assertOk();
        $this->assertTrue(json_decode($response->getContent())->statuses !== null);

        //test global dashboard. User Gertrud should not be seen, because it is a private profile
        $globalDashboard = DashboardController::getGlobalDashboard($gertrud);
        $userIds         = [];
        foreach ($globalDashboard as $dashboard) {
            $userIds[] = $dashboard->user_id;
        }
        $this->assertTrue(in_array($gertrud->id, $userIds));

        //check that statuses from other private users are not visible for user Gertrud
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->json('GET', route('api.v0.user', ['username' => 'Alice123']));
        $response->assertOk();
        $this->assertTrue(json_decode($response->getContent())->statuses == null);

    }
}
