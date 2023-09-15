<?php

namespace Tests\Feature;

use App\Models\TrainCheckin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\ApiTestCase;

class ExportTripsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_pdf_export(): void {
        $user = User::factory()->create();
        TrainCheckin::factory(['user_id' => $user->id])->count(2)->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->postJson(
            uri:     '/api/v1/statistics/export',
            data:    [
                         'from'     => Date::today()->subWeek(),
                         'until'    => Date::today()->addWeek(),
                         'filetype' => 'pdf'
                     ],
            headers: ['Authorization' => 'Bearer ' . $token]
        );
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_json_export(): void {
        $user = User::factory()->create();
        TrainCheckin::factory(['user_id' => $user->id])->count(2)->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->postJson(
            uri:     '/api/v1/statistics/export',
            data:    [
                         'from'     => Date::today()->subWeek(),
                         'until'    => Date::today()->addWeek(),
                         'filetype' => 'json'
                     ],
            headers: ['Authorization' => 'Bearer ' . $token]
        );
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/json; charset=UTF-8');
    }

    public function test_csv_export(): void {
        $user = User::factory()->create();
        TrainCheckin::factory(['user_id' => $user->id])->count(2)->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->postJson(
            uri:     '/api/v1/statistics/export',
            data:    [
                         'from'     => Date::today()->subWeek(),
                         'until'    => Date::today()->addWeek(),
                         'filetype' => 'csv'
                     ],
            headers: ['Authorization' => 'Bearer ' . $token]
        );
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
