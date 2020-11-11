<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
Use \Illuminate\Support\Facades\File;
use Tests\ApiTestCase;

class ApiUserTest extends ApiTestCase
{
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        $this->loginGertrudAndAckGDPR();
    }

    /**
     * Create a user with the api
     * @test
     */
    public function change_profile_picture() {
        $file     = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
            ->call('put',
                   route('api.v0.user.profilepicture'),
                   [],
                   [],
                   [],
                   [],
                   'data:image/jpeg;base64,'.base64_encode(file_get_contents($file)));
        $response->assertOk();
        $user = User::where('username', 'Gertrud123')->first();

        $profilePictureName = $user->avatar;
        $this->assertTrue(File::exists(public_path('/uploads/avatars/'.$profilePictureName)));
        //Wait 1 second so that timestamp is updated and upload second file and check if first file was deleted.
        sleep(1);
        $file2    = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
            ->call('put',
                   route('api.v0.user.profilepicture'),
                   [],
                   [],
                   [],
                   [],
                   'data:image/jpeg;base64,'.base64_encode(file_get_contents($file2)));
        $response->assertOk();

        $user                  = User::where('username', 'Gertrud123')->first();
        $newProfilePictureName = $user->avatar;
        //Check if old profile picture has been deleted
        $this->assertFalse(File::exists(public_path('/uploads/avatars/'.$profilePictureName)));
        $this->assertTrue(File::exists(public_path('/uploads/avatars/'.$newProfilePictureName)));

    }

    /**
     * Update the display name via the api
     * @test
     */
    public function update_displayname() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
            ->call('put',
                   route('api.v0.user.displayname'),
                   [],
                   [],
                   [],
                   [],
                   'Gertrud von Träwelling');
        $response->assertOk();
        $user = User::where('username', 'Gertrud123')->first();
        $this->assertTrue($user->name == 'Gertrud von Träwelling');
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
            'user' => [
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
            'user' => [
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
                'origin' => [
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
                'delay',
                'hafas_trip' => [
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
            'event']);
    }

    /**
     * Test the leaderboard endpoint
     * @test
     */
    public function get_leaderboard() {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get(route('api.v0.user.leaderboard'));

        $response->assertOk();
        $this->assertFalse(empty(json_decode($response->getContent(), true)));
        $response->assertJsonStructure([
            "usersCount",
            "users",
            "friends",
            "kilometers"
        ]);

    }

    /**
     * Test the user search
     * @test
     */
    public function get_user_search() {
        //Test that user has been found
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
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
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
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
}
