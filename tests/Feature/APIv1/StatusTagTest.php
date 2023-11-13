<?php

namespace Tests\Feature\APIv1;

use App\Enum\StatusVisibility;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StatusTagTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testViewNonExistingTagsOnOwnStatus(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $status = Status::factory(['user_id' => $user->id])->create();

        $response = $this->get('/api/v1/status/' . $status->id . '/tags');
        $response->assertJsonStructure(['data' => []]);
        $response->assertJsonCount(0, 'data');
    }

    public function testViewTagsOnOwnStatusWithDifferentVisibilitiesAndDeleteOne(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $status      = Status::factory(['user_id' => $user->id])->create();
        $tagToDelete = StatusTag::factory(['status_id' => $status->id, 'key' => 'first', 'visibility' => StatusVisibility::PUBLIC->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => 'second', 'visibility' => StatusVisibility::FOLLOWERS->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => 'third', 'visibility' => StatusVisibility::PRIVATE->value])->create();
        StatusTag::factory(['status_id' => $status->id, 'key' => 'fourth', 'visibility' => StatusVisibility::AUTHENTICATED->value])->create();

        $response = $this->get('/api/v1/status/' . $status->id . '/tags');
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
        $response = $this->delete('/api/v1/status/' . $status->id . '/tags/' . $tagToDelete->key);
        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('status_tags', ['id' => $tagToDelete->id]);
    }

    public function testCreateAndUpdateTag(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $status = Status::factory(['user_id' => $user->id])->create();

        //Create StatusTag
        $response = $this->post(
            uri:  '/api/v1/status/' . $status->id . '/tags',
            data: [
                      'key'        => 'test',
                      'value'      => 'test',
                      'visibility' => StatusVisibility::PUBLIC->value,
                  ],
        );
        $response->assertOk();
        $response->assertJson([
                                  'data' => [
                                      'key'        => 'test',
                                      'value'      => 'test',
                                      'visibility' => StatusVisibility::PUBLIC->value,
                                  ]
                              ]);

        $this->assertDatabaseHas('status_tags', ['status_id' => $status->id, 'key' => 'test', 'value' => 'test', 'visibility' => StatusVisibility::PUBLIC->value]);

        //Update StatusTag and change key and value
        $response = $this->put(
            uri:  '/api/v1/status/' . $status->id . '/tags/test',
            data: [
                      'key'   => 'test2',
                      'value' => 'test2',
                  ],
        );
        $response->assertOk();
        $response->assertJson([
                                  'data' => [
                                      'key'        => 'test2',
                                      'value'      => 'test2',
                                      'visibility' => StatusVisibility::PUBLIC->value,
                                  ]
                              ]);

        $this->assertDatabaseMissing('status_tags', ['status_id' => $status->id, 'key' => 'test', 'value' => 'test', 'visibility' => StatusVisibility::PUBLIC->value]);
        $this->assertDatabaseHas('status_tags', ['status_id' => $status->id, 'key' => 'test2', 'value' => 'test2', 'visibility' => StatusVisibility::PUBLIC->value]);
    }
}
