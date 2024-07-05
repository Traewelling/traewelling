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

        $this->expectException(AlreadyAcceptedException::class);
        PrivacyPolicyService::acceptPrivacyPolicy($user);
    }
}
