<?php

namespace Tests\Feature\APIv1;

use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class ReportTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_report_status(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $status = Status::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/report', [
            'subjectType' => 'Status',
            'subjectId' => $status->id,
            'reason' => 'inappropriate',
            'description' => 'The status is inappropriate because it contains offensive language.',
        ]);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_report_status_without_description_fails(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $status = Status::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/report', [
            'subjectType' => 'Status',
            'subjectId' => $status->id,
            'reason' => 'inappropriate',
        ]);
        $response->assertStatus(422);
    }

    public function test_report_status_with_short_description_fails(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $status = Status::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/report', [
            'subjectType' => 'Status',
            'subjectId' => $status->id,
            'reason' => 'inappropriate',
            'description' => 'too short',
        ]);
        $response->assertStatus(422);
    }
}
