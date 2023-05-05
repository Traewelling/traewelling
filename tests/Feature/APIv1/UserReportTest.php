<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserReportTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserReportCreation(): void {
        $userWhoIsReporting = User::factory()->create();
        $userWhoIsReported  = User::factory()->create();

        //Case 1: Not logged in
        $this->assertGuest();
        $response = $this->postJson(
            uri: '/api/v1/user/' . $userWhoIsReporting->id . '/report'
        );
        $response->assertUnauthorized();

        //Case 2: Self report
        $response = $this->actingAs($userWhoIsReporting, 'web')
                         ->postJson(
                             uri:  '/api/v1/user/' . $userWhoIsReporting->id . '/report',
                             data: ['message' => 'I don\t like this user!'],
                         );
        $response->assertBadRequest();
        $this->assertEquals(__('user.report.self_report'), $response->json('message'));

        //Case 3: Correct report
        $response = $this->actingAs($userWhoIsReporting, 'web')
                         ->postJson(
                             uri:  '/api/v1/user/' . $userWhoIsReported->id . '/report',
                             data: ['message' => 'I don\t like this user!'],
                         );
        $response->assertOk();
        $this->assertEquals(__('user.report.sent'), $response->json('data'));
    }
}
