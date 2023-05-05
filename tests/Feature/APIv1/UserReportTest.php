<?php

namespace Tests\Feature\APIv1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Tests\ApiTestCase;
use App\Providers\AuthServiceProvider;
use Tests\TestCase;

class UserReportTest extends TestCase
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

        //ToDo: Currently failing because of cookie auth
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
