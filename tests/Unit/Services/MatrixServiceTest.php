<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\MatrixException;
use App\Services\MatrixService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\Unit\UnitTestCase;

class MatrixServiceTest extends UnitTestCase
{
    private const HOMESERVER = 'https://matrix.example.org';

    private const TOKEN = 'syt_test_token';

    private const ROOM_ID = '!abc123:example.org';

    public function test_is_admin_active_reflects_config(): void
    {
        config(['services.matrix.admin.homeserver' => self::HOMESERVER, 'services.matrix.admin.token' => self::TOKEN]);
        $this->assertTrue(MatrixService::isAdminActive());

        config(['services.matrix.admin.homeserver' => null]);
        $this->assertFalse(MatrixService::isAdminActive());
    }

    public function test_admin_factories_return_null_when_not_active(): void
    {
        config(['services.matrix.admin.homeserver' => null, 'services.matrix.admin.token' => null]);

        $this->assertNull(MatrixService::adminForEvents());
        $this->assertNull(MatrixService::adminForReports());
    }

    public function test_admin_factories_return_null_when_room_id_missing(): void
    {
        config([
            'services.matrix.admin.homeserver' => self::HOMESERVER,
            'services.matrix.admin.token' => self::TOKEN,
            'services.matrix.admin.events_room_id' => null,
            'services.matrix.admin.reports_room_id' => null,
        ]);

        $this->assertNull(MatrixService::adminForEvents());
        $this->assertNull(MatrixService::adminForReports());
    }

    public function test_admin_factories_return_instance_when_configured(): void
    {
        config([
            'services.matrix.admin.homeserver' => self::HOMESERVER,
            'services.matrix.admin.token' => self::TOKEN,
            'services.matrix.admin.events_room_id' => self::ROOM_ID,
            'services.matrix.admin.reports_room_id' => self::ROOM_ID,
        ]);

        $this->assertInstanceOf(MatrixService::class, MatrixService::adminForEvents());
        $this->assertInstanceOf(MatrixService::class, MatrixService::adminForReports());
    }

    public function test_send_message_converts_newlines_to_br_and_returns_event_id(): void
    {
        Http::fake(['https://matrix.example.org/*' => Http::response(['event_id' => '$abc123'], 200)]);

        $service = new MatrixService(self::HOMESERVER, self::TOKEN, self::ROOM_ID);
        $eventId = $service->sendMessage("<b>Title</b>\nBody line");

        $this->assertSame('$abc123', $eventId);
        Http::assertSent(function ($request) {
            return $request['formatted_body'] === '<b>Title</b><br>Body line'
                && $request['body'] === "Title\nBody line";
        });
    }

    public function test_send_message_adds_https_scheme_when_missing(): void
    {
        Http::fake(['https://matrix.example.org/*' => Http::response(['event_id' => '$xyz'], 200)]);
        Log::spy();

        $service = new MatrixService('matrix.example.org', self::TOKEN, self::ROOM_ID);
        $service->sendMessage('Hello');

        Log::shouldHaveReceived('warning')->once();
        Http::assertSentCount(1);
    }

    public function test_send_message_throws_on_api_error(): void
    {
        Http::fake(['https://matrix.example.org/*' => Http::response('Forbidden', 403)]);

        $service = new MatrixService(self::HOMESERVER, self::TOKEN, self::ROOM_ID);

        $this->expectException(MatrixException::class);
        $service->sendMessage('Hello');
    }

    public function test_redact_message_returns_true_on_success(): void
    {
        Http::fake(['https://matrix.example.org/*' => Http::response(['event_id' => '$redact'], 200)]);

        $service = new MatrixService(self::HOMESERVER, self::TOKEN, self::ROOM_ID);

        $this->assertTrue($service->redactMessage('$original_event_id'));
    }

    public function test_redact_message_throws_on_api_error(): void
    {
        Http::fake(['https://matrix.example.org/*' => Http::response('Unauthorized', 401)]);

        $service = new MatrixService(self::HOMESERVER, self::TOKEN, self::ROOM_ID);

        $this->expectException(MatrixException::class);
        $service->redactMessage('$event_id');
    }

    public function test_send_message_wraps_connection_exception_as_matrix_exception(): void
    {
        Http::fake(['https://matrix.example.org/*' => Http::failedConnection()]);

        $service = new MatrixService(self::HOMESERVER, self::TOKEN, self::ROOM_ID);

        $this->expectException(MatrixException::class);
        $service->sendMessage('Hello');
    }

    public function test_redact_message_wraps_connection_exception_as_matrix_exception(): void
    {
        Http::fake(['https://matrix.example.org/*' => Http::failedConnection()]);

        $service = new MatrixService(self::HOMESERVER, self::TOKEN, self::ROOM_ID);

        $this->expectException(MatrixException::class);
        $service->redactMessage('$event_id');
    }
}
