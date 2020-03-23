<?php

use App\User;
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
        $file = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
            ->call('put', route('api.v0.user.profilepicture'), [], [], [], [],  'data:image/jpeg;base64,'.base64_encode(file_get_contents($file)));
        $response->assertOk();
        $user = User::where('username', 'Gertrud123')->first();

        $profilePictureName = $user->avatar;
        $this->assertTrue(File::exists(public_path('/uploads/avatars/'.$profilePictureName)));
        //Wait 1 second so that timestamp is updated and upload second file and check if first file was deleted.
        sleep(1);
        $file2 = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
            ->call('put', route('api.v0.user.profilepicture'), [], [], [], [],  'data:image/jpeg;base64,'.base64_encode(file_get_contents($file2)));
        $response->assertOk();

        $user = User::where('username', 'Gertrud123')->first();
        $newProfilePictureName = $user->avatar;
        //Check if old profile picture has been deleted
        $this->assertFalse(File::exists(public_path('/uploads/avatars/'.$profilePictureName)));
        $this->assertTrue(File::exists(public_path('/uploads/avatars/'.$newProfilePictureName)));

    }

    public function update_displayname() {
        //ToDo
    }

    public function get_user_profile() {
        //ToDo
    }

    public function get_user() {
        //ToDo
    }

    public function get_active_checkin_for_user() {
        //ToDo
    }
}
