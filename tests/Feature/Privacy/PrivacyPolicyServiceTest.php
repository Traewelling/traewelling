<?php

namespace Tests\Feature\Privacy;

use App\Exceptions\AlreadyAcceptedException;
use App\Models\User;
use App\Services\PrivacyPolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class PrivacyPolicyServiceTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testAcceptPrivacyPolicy(): void {
        $user = User::factory(['privacy_ack_at' => null])->create();
        $this->assertNull($user->privacy_ack_at);

        PrivacyPolicyService::acceptPrivacyPolicy($user);

        $this->assertNotNull($user->refresh()->privacy_ack_at);

        // try/catch exception (not using assertThrows) to get the exception object
        try {
            PrivacyPolicyService::acceptPrivacyPolicy($user);
            $this->fail('Expected AlreadyAcceptedException, but no exception was thrown');
        } catch (AlreadyAcceptedException $exception) {
            $this->assertEquals($user->privacy_ack_at, $exception->getUserAccepted());
            $this->assertEquals(PrivacyPolicyService::getCurrentPrivacyPolicy()->valid_at, $exception->getPrivacyValidity());
        }
    }
}
