<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class ProfilePictureTest extends ApiTestCase
{
    use RefreshDatabase;

    private const MINIMAL_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    public function test_returns_generated_default_avatar_for_user_without_avatar(): void
    {
        $user = User::factory(['avatar' => null])->create();

        $response = $this->get(route('profile.picture', ['username' => $user->username]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertNotEmpty($response->getContent());
    }

    public function test_returns_uploaded_avatar_when_set(): void
    {
        $uploadDir = public_path('/uploads/avatars');
        File::ensureDirectoryExists($uploadDir);

        // Create a minimal 1x1 PNG
        $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $avatarFile = 'test_feature_' . time() . '.png';
        File::put($uploadDir . '/' . $avatarFile, $pngBytes);

        $user = User::factory(['avatar' => $avatarFile])->create();

        $response = $this->get(route('profile.picture', ['username' => $user->username]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');

        File::delete($uploadDir . '/' . $avatarFile);
    }

    public function test_returns_default_avatar_when_avatar_file_is_missing(): void
    {
        $user = User::factory(['avatar' => 'nonexistent_file.png'])->create();

        $response = $this->get(route('profile.picture', ['username' => $user->username]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertNotEmpty($response->getContent());
    }

    public function test_returns_404_for_nonexistent_user(): void
    {
        $response = $this->get(route('profile.picture', ['username' => 'nonexistent_user_xyz']));

        $response->assertNotFound();
    }

    public function test_upload_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/settings/profile-picture', [
            'image' => self::MINIMAL_PNG,
        ]);

        $response->assertUnauthorized();
    }

    public function test_upload_stores_image(): void
    {
        $uploadDir = public_path('/uploads/avatars');
        File::ensureDirectoryExists($uploadDir);

        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/settings/profile-picture', [
            'image' => self::MINIMAL_PNG,
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertTrue(File::exists($uploadDir . '/' . $user->avatar));

        File::delete($uploadDir . '/' . $user->avatar);
    }

    public function test_upload_replaces_old_avatar(): void
    {
        $uploadDir = public_path('/uploads/avatars');
        File::ensureDirectoryExists($uploadDir);

        $oldAvatar = 'old_avatar.png';
        File::put($uploadDir . '/' . $oldAvatar, 'dummy');

        $user = User::factory(['avatar' => $oldAvatar])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/settings/profile-picture', [
            'image' => self::MINIMAL_PNG,
        ]);

        $response->assertOk();
        $this->assertFalse(File::exists($uploadDir . '/' . $oldAvatar));

        $user->refresh();
        if ($user->avatar) {
            File::delete($uploadDir . '/' . $user->avatar);
        }
    }

    public function test_upload_rejects_unsupported_image_format(): void
    {
        $user = User::factory(['avatar' => null])->create();
        Passport::actingAs($user, ['*']);

        $svg = 'data:image/svg+xml;base64,'
            . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>');

        $response = $this->postJson('/api/v1/settings/profile-picture', [
            'image' => $svg,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('message', __('errors.unsupported-image'));
        $this->assertNull($user->fresh()->avatar);
    }

    public function test_upload_requires_image(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/settings/profile-picture', []);

        $response->assertUnprocessable();
    }

    public function test_delete_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/v1/settings/profile-picture');

        $response->assertUnauthorized();
    }

    public function test_delete_removes_avatar(): void
    {
        $uploadDir = public_path('/uploads/avatars');
        File::ensureDirectoryExists($uploadDir);

        $avatarFile = 'test_avatar_' . time() . '.png';
        File::put($uploadDir . '/' . $avatarFile, 'dummy');

        $user = User::factory(['avatar' => $avatarFile])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->deleteJson('/api/v1/settings/profile-picture');

        $response->assertOk();
        $this->assertNull($user->fresh()->avatar);
        $this->assertFalse(File::exists($uploadDir . '/' . $avatarFile));
    }

    public function test_delete_returns_error_when_no_avatar(): void
    {
        $user = User::factory(['avatar' => null])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->deleteJson('/api/v1/settings/profile-picture');

        $response->assertStatus(400);
    }
}
