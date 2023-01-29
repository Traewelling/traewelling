<?php

namespace Tests\Feature\APIv1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Tests\ApiTestCase;

class StatusTagTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testViewNonExistingTagsOnOwnStatus(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token')->accessToken;
        $status    = Status::factory(['user_id' => $user->id])->create();

        $response = $this->get(
            uri:     '/api/v1/statuses/' . $status->id . '/tags',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertJsonStructure(['data' => []]);
        $response->assertJsonCount(0, 'data');
    }

    public function testViewTagsOnOwnStatusWithDifferentVisibilitiesAndDeleteOne(): void {
        $user        = User::factory()->create();
        $userToken   = $user->createToken('token')->accessToken;
        $status      = Status::factory(['user_id' => $user->id])->create();
        $tagToDelete = StatusTag::factory(['status_id' => $status->id, 'visibility' => StatusVisibility::PUBLIC->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'visibility' => StatusVisibility::FOLLOWERS->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'visibility' => StatusVisibility::PRIVATE->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'visibility' => StatusVisibility::AUTHENTICATED->value])->create();

        $response = $this->get(
            uri:     '/api/v1/statuses/' . $status->id . '/tags',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertJsonStructure([
                                           'data' => [
                                               '*' => [
                                                   'key',
                                                   'value',
                                                   'visibility',
                                               ]
                                           ]
                                       ]);
        $response->assertJsonCount(4, 'data');

        $this->assertDatabaseHas('status_tags', ['id' => $tagToDelete->id]);

        //Delete StatusTag
        $response = $this->delete(
            uri:     '/api/v1/statuses/' . $status->id . '/tags/' . $tagToDelete->key,
            headers: ['Authorization' => 'Bearer ' . $userToken],
        );
        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('status_tags', ['id' => $tagToDelete->id]);
    }

    public function testCreateAndUpdateTag(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token')->accessToken;
        $status    = Status::factory(['user_id' => $user->id])->create();

        //Create StatusTag
        $response = $this->post(
            uri:     '/api/v1/statuses/' . $status->id . '/tags',
            data:    [
                         'key'        => 'test',
                         'value'      => 'test',
                         'visibility' => StatusVisibility::PUBLIC->name,
                     ],
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJson([
                                  'data' => [
                                      'key'        => 'test',
                                      'value'      => 'test',
                                      'visibility' => StatusVisibility::PUBLIC->name,
                                  ]
                              ]);

        $this->assertDatabaseHas('status_tags', ['status_id' => $status->id, 'key' => 'test', 'value' => 'test', 'visibility' => StatusVisibility::PUBLIC->value]);

        //Update StatusTag and change key and value
        $response = $this->put(
            uri:     '/api/v1/statuses/' . $status->id . '/tags/test',
            data:    [
                         'key'   => 'test2',
                         'value' => 'test2',
                     ],
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJson([
                                  'data' => [
                                      'key'        => 'test2',
                                      'value'      => 'test2',
                                      'visibility' => StatusVisibility::PUBLIC->name,
                                  ]
                              ]);

        $this->assertDatabaseMissing('status_tags', ['status_id' => $status->id, 'key' => 'test', 'value' => 'test', 'visibility' => StatusVisibility::PUBLIC->value]);
        $this->assertDatabaseHas('status_tags', ['status_id' => $status->id, 'key' => 'test2', 'value' => 'test2', 'visibility' => StatusVisibility::PUBLIC->value]);

    }
}
