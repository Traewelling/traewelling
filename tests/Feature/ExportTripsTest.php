<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class ExportTripsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_pdf_export(): void
    {
        $user = User::factory()->create();
        Checkin::factory(['user_id' => $user->id])->count(2)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson(
            uri: '/api/v1/export/statuses',
            data: [
                'from' => Date::today()->subWeek(),
                'until' => Date::today()->addWeek(),
                'filetype' => 'pdf',
            ],
        );
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_json_export(): void
    {
        $user = User::factory()->create();
        Checkin::factory(['user_id' => $user->id])->count(2)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson(
            uri: '/api/v1/export/statuses',
            data: [
                'from' => Date::today()->subWeek(),
                'until' => Date::today()->addWeek(),
                'filetype' => 'json',
            ],
        );
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/json; charset=utf-8');
    }

    public function test_csv_export(): void
    {
        $user = User::factory()->create();
        Checkin::factory(['user_id' => $user->id])->count(2)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson(
            uri: '/api/v1/export/statuses',
            data: [
                'from' => Date::today()->subWeek(),
                'until' => Date::today()->addWeek(),
                'filetype' => 'csv_machine',
            ],
        );
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; header=present; charset=utf-8');
    }

    /**
     * RFC 4180: lines are terminated with CRLF (\r\n).
     *
     * @see https://www.rfc-editor.org/rfc/rfc4180
     */
    public function test_csv_export_uses_crlf_line_endings(): void
    {
        $user = User::factory()->create();
        Checkin::factory(['user_id' => $user->id])->create();
        Passport::actingAs($user, ['*']);

        $response = $this->postJson(
            uri: '/api/v1/export/statuses',
            data: [
                'from' => Date::today()->subWeek(),
                'until' => Date::today()->addWeek(),
                'filetype' => 'csv_machine',
                'columns' => ['body'],
            ],
        );
        $response->assertSuccessful();

        $csv = $response->streamedContent();
        $this->assertStringContainsString("\r\n", $csv, 'RFC 4180 requires CRLF line endings');
        $this->assertStringNotContainsString("\r\n\r\n", $csv, 'No duplicate CRLF between rows');
    }

    /**
     * RFC 4180: fields containing commas, newlines or double quotes must be enclosed
     * in double quotes. Double quotes within fields are escaped by doubling them ("").
     *
     * @see https://www.rfc-editor.org/rfc/rfc4180
     */
    public function test_csv_export_quotes_special_characters_in_body_per_rfc4180(): void
    {
        $user = User::factory()->create();

        // Body with comma: must be enclosed in double quotes
        $checkinComma = Checkin::factory(['user_id' => $user->id])->create();
        $checkinComma->status->update(['body' => 'Hamburg, Berlin']);

        // Body with newline: must be enclosed in double quotes, newline preserved inside
        $checkinNewline = Checkin::factory(['user_id' => $user->id])->create();
        $checkinNewline->status->update(['body' => "First line\nSecond line"]);

        // Body with double quote: escaped by doubling (" becomes "")
        $checkinQuote = Checkin::factory(['user_id' => $user->id])->create();
        $checkinQuote->status->update(['body' => 'Say "hello"']);

        Passport::actingAs($user, ['*']);

        $response = $this->postJson(
            uri: '/api/v1/export/statuses',
            data: [
                'from' => Date::today()->subWeek(),
                'until' => Date::today()->addWeek(),
                'filetype' => 'csv_machine',
                'columns' => ['body'],
            ],
        );
        $response->assertSuccessful();

        $csv = $response->streamedContent();

        // Field with comma must be enclosed in double quotes
        $this->assertStringContainsString('"Hamburg, Berlin"', $csv);

        // Field with newline must be enclosed in double quotes, newline preserved inside
        $this->assertMatchesRegularExpression('/"First line\r?\nSecond line"/', $csv);

        // Double quote encoded as "" inside a quoted field
        $this->assertStringContainsString('"Say ""hello"""', $csv);
    }
}
